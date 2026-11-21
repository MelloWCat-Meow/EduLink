<?php

namespace Tests\Feature\Pesanan;

use App\Enums\JenisNotifikasi;
use App\Enums\StatusJadwalUlang;
use App\Models\AkunPengguna;
use App\Models\PermintaanJadwalUlang;
use App\Models\Pesanan;
use App\Models\SesiPembelajaran;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Notifications\Pemberitahuan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

class JadwalUlangTest extends TestCase
{
    use MembuatPesanan, RefreshDatabase;

    private Tutor $tutor;

    private AkunPengguna $ortu;

    private Pesanan $pesanan;

    private SesiPembelajaran $sesi;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->travelTo(Carbon::parse('2026-11-23 08:00'));

        $this->tutor = Tutor::factory()->siapDipesan()->denganAkun()->create()->load('profil', 'mataPelajaran', 'jadwal');
        $siswa = Siswa::factory()->create();
        $this->ortu = $siswa->wali;
        $this->pesanan = $this->pesananDibayar($siswa, $this->tutor, ['jumlah_pertemuan' => 3]);
        $this->sesi = $this->pesanan->sesi()->orderBy('waktu')->skip(1)->firstOrFail();
    }

    /**
     * @return array{tanggal: string, jam: string, alasan: string}
     */
    private function isian(Carbon $waktu, string $alasan = 'Ada ujian sekolah hari itu.'): array
    {
        return ['tanggal' => $waktu->toDateString(), 'jam' => $waktu->format('H:i'), 'alasan' => $alasan];
    }

    private function waktuBaru(int $tambahHari = 2): Carbon
    {
        return $this->sesi->waktu->copy()->addDays($tambahHari)->setTime(10, 0);
    }

    private function ajukan(AkunPengguna $oleh, ?Carbon $waktu = null): TestResponse
    {
        return $this->actingAs($oleh)->post(route('jadwal-ulang.ajukan', $this->sesi), $this->isian($waktu ?? $this->waktuBaru()));
    }

    public function test_parent_asks_tutor_approves_and_the_session_moves(): void
    {
        $baru = $this->waktuBaru();

        $this->ajukan($this->ortu)->assertSessionHas('status');
        $permintaan = PermintaanJadwalUlang::query()->sole();
        $this->assertSame(StatusJadwalUlang::Menunggu, $permintaan->status);
        Notification::assertSentTo($this->tutor->akun, Pemberitahuan::class, fn (Pemberitahuan $n) => $n->jenis === JenisNotifikasi::JadwalUlangDiajukan);

        $this->actingAs($this->tutor->akun)->get(route('tutor.pesanan.show', $this->pesanan))->assertSeeText('Setujui jadwal baru');

        $this->actingAs($this->tutor->akun)->post(route('jadwal-ulang.setujui', $permintaan))->assertSessionHas('status');

        $this->assertSame($baru->toDateTimeString(), $this->sesi->refresh()->waktu->toDateTimeString());
        $this->assertSame(StatusJadwalUlang::Disetujui, $permintaan->refresh()->status);
        $this->assertNotNull($permintaan->dijawab_pada);
        Notification::assertSentTo($this->ortu, Pemberitahuan::class, fn (Pemberitahuan $n) => $n->jenis === JenisNotifikasi::JadwalUlangDijawab);
        $this->assertTrue($this->pesanan->riwayat()->where('catatan', 'like', 'Jadwal ulang disetujui%')->exists());
        $this->actingAs($this->ortu)->get(route('pesanan.show', $this->pesanan))->assertSeeText('Dijadwalkan ulang 1 kali');
    }

    public function test_tutor_asks_parent_rejects_and_the_session_stays(): void
    {
        $lama = $this->sesi->waktu->toDateTimeString();

        $this->ajukan($this->tutor->akun);
        $permintaan = PermintaanJadwalUlang::query()->sole();

        $this->actingAs($this->ortu)->post(route('jadwal-ulang.tolak', $permintaan), ['alasan' => 'Anak ada les lain hari itu.']);

        $this->assertSame(StatusJadwalUlang::Ditolak, $permintaan->refresh()->status);
        $this->assertSame('Anak ada les lain hari itu.', $permintaan->alasan_penolakan);
        $this->assertSame($lama, $this->sesi->refresh()->waktu->toDateTimeString());
        Notification::assertSentTo($this->tutor->akun, Pemberitahuan::class, fn (Pemberitahuan $n) => $n->jenis === JenisNotifikasi::JadwalUlangDijawab);
    }

    public function test_requests_close_twenty_four_hours_before_the_session(): void
    {
        $this->travelTo($this->sesi->waktu->copy()->subHours(23));

        $this->ajukan($this->ortu, $this->sesi->waktu->copy()->addDays(3))
            ->assertSessionHas('galat', 'Jadwal ulang paling lambat diajukan 24 jam sebelum sesi dimulai.');
        $this->assertSame(0, PermintaanJadwalUlang::query()->count());
    }

    public function test_the_new_time_must_be_at_least_a_day_ahead_and_within_fourteen_days_of_the_old_one(): void
    {
        $this->ajukan($this->ortu, now()->addHours(5))->assertSessionHas('galat', fn (string $pesan) => str_contains($pesan, '24 jam dari sekarang'));
        $this->ajukan($this->ortu, $this->sesi->waktu->copy()->addDays(15))->assertSessionHas('galat', fn (string $pesan) => str_contains($pesan, '14 hari'));
        $this->assertSame(0, PermintaanJadwalUlang::query()->count());
    }

    public function test_only_one_request_may_wait_per_session(): void
    {
        $this->ajukan($this->ortu);
        $this->ajukan($this->tutor->akun, $this->waktuBaru(3))->assertSessionHas('galat', 'Sesi ini sudah punya pengajuan jadwal ulang yang menunggu jawaban.');

        $this->assertSame(1, PermintaanJadwalUlang::query()->count());
    }

    public function test_a_time_that_clashes_with_another_session_of_the_tutor_is_refused(): void
    {
        $lain = $this->pesananDibayar(null, $this->tutor->load('jadwal')->setRelation('jadwal', collect([$this->tutor->jadwal()->create(['hari' => 'minggu', 'jam' => '08:00:00', 'status' => 'tersedia'])])));
        $sesiLain = $lain->sesi()->orderBy('waktu')->firstOrFail();

        $this->ajukan($this->ortu, $sesiLain->waktu->copy()->addMinutes(30))->assertSessionHas('galat', fn (string $pesan) => str_contains($pesan, 'bentrok'));
        $this->assertSame(0, PermintaanJadwalUlang::query()->count());

        $this->ajukan($this->ortu, $sesiLain->waktu->copy()->addMinutes(60 + $lain->durasi))->assertSessionHas('status');
    }

    public function test_a_session_moves_at_most_twice(): void
    {
        foreach ([2, 4] as $hari) {
            $this->ajukan($this->ortu, $this->waktuBaru($hari));
            $this->actingAs($this->tutor->akun)->post(route('jadwal-ulang.setujui', PermintaanJadwalUlang::query()->latest('id')->first()));
            $this->sesi->refresh();
        }

        $this->ajukan($this->ortu, $this->waktuBaru(1))->assertSessionHas('galat', 'Sesi ini sudah dijadwalkan ulang 2 kali, batas maksimalnya.');
        $this->assertSame(2, PermintaanJadwalUlang::query()->count());
    }

    public function test_an_unanswered_request_expires_when_the_old_time_arrives(): void
    {
        $lama = $this->sesi->waktu->toDateTimeString();
        $this->ajukan($this->ortu);
        $permintaan = PermintaanJadwalUlang::query()->sole();

        $this->travelTo($this->sesi->waktu->copy()->addMinute());
        $this->artisan('edulink:jadwal-ulang-kedaluwarsa')->assertSuccessful();

        $this->assertSame(StatusJadwalUlang::Kedaluwarsa, $permintaan->refresh()->status);
        $this->actingAs($this->tutor->akun)->post(route('jadwal-ulang.setujui', $permintaan))->assertSessionHas('galat');
        $this->assertSame($lama, $this->sesi->refresh()->waktu->toDateTimeString());
    }

    public function test_only_the_other_party_of_the_order_may_answer(): void
    {
        $this->ajukan($this->ortu);
        $permintaan = PermintaanJadwalUlang::query()->sole();

        $this->actingAs($this->ortu)->post(route('jadwal-ulang.setujui', $permintaan))->assertSessionHas('galat');
        $this->actingAs(AkunPengguna::factory()->ortu()->create())->post(route('jadwal-ulang.setujui', $permintaan))->assertSessionHas('galat');
        $this->actingAs(Tutor::factory()->denganAkun()->create()->akun)->post(route('jadwal-ulang.tolak', $permintaan), ['alasan' => 'Bukan urusan saya.'])->assertSessionHas('galat');
        $this->actingAs(AkunPengguna::factory()->admin()->create())->post(route('jadwal-ulang.setujui', $permintaan))->assertForbidden();

        $this->assertSame(StatusJadwalUlang::Menunggu, $permintaan->refresh()->status);
    }

    public function test_someone_outside_the_order_cannot_ask(): void
    {
        $this->ajukan(AkunPengguna::factory()->ortu()->create())->assertSessionHas('galat');
        $this->ajukan(Tutor::factory()->denganAkun()->create()->akun)->assertSessionHas('galat');

        $this->assertSame(0, PermintaanJadwalUlang::query()->count());
    }
}
