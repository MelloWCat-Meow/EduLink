<?php

namespace Tests\Feature\Pesanan;

use App\Enums\JenisKeluhan;
use App\Enums\JenisNotifikasi;
use App\Enums\Kehadiran;
use App\Enums\StatusKeluhan;
use App\Enums\StatusKonfirmasiSesi;
use App\Enums\StatusPesanan;
use App\Models\AkunPengguna;
use App\Models\Keluhan;
use App\Models\Pesanan;
use App\Models\SesiPembelajaran;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Notifications\Pemberitahuan;
use App\Services\AlurPesanan;
use App\Services\LaporanRingkasan;
use App\Services\SaldoTutor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

class KonfirmasiSesiTest extends TestCase
{
    use MembuatPesanan, RefreshDatabase;

    private Tutor $tutor;

    private AkunPengguna $ortu;

    private AkunPengguna $admin;

    private Pesanan $pesanan;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->admin = AkunPengguna::factory()->admin()->create();
        $this->tutor = Tutor::factory()->siapDipesan(['tarif' => '100000.00'])->denganAkun()->create()->load('profil', 'mataPelajaran', 'jadwal');
        $siswa = Siswa::factory()->create();
        $this->ortu = $siswa->wali;
        $this->pesanan = $this->pesananDibayar($siswa, $this->tutor, ['jumlah_pertemuan' => 2]);
    }

    private function catat(int $urutan = 0, Kehadiran $kehadiran = Kehadiran::Hadir): SesiPembelajaran
    {
        $sesi = $this->pesanan->sesi()->orderBy('waktu')->skip($urutan)->firstOrFail();
        app(AlurPesanan::class)->catatKehadiran($sesi, $kehadiran, 'Bab pecahan.', periksaWaktu: false);

        return $sesi->refresh();
    }

    public function test_logging_a_session_waits_for_the_parent(): void
    {
        $this->freezeTime();
        $sesi = $this->catat();

        $this->assertSame(StatusKonfirmasiSesi::MenungguKonfirmasi, $sesi->status_konfirmasi);
        $this->assertSame(now()->toDateTimeString(), $sesi->dicatat_pada->toDateTimeString());
        $this->assertSame(now()->addHours(48)->toDateTimeString(), $sesi->batasKonfirmasi()->toDateTimeString());

        $this->actingAs($this->ortu)->get(route('beranda'))->assertSeeText('Sesi menunggu konfirmasi Anda (1)')->assertSeeText('Sisa waktu konfirmasi');
        $this->actingAs($this->ortu)->get(route('pesanan.show', $this->pesanan))->assertSeeText('Konfirmasi sesi')->assertSeeText('Sanggah');
        $this->actingAs($this->ortu)->get(route('notifikasi.index'));
    }

    public function test_the_parent_confirms_and_the_order_finishes_once_every_session_is_settled(): void
    {
        $pertama = $this->catat(0);
        $kedua = $this->catat(1, Kehadiran::TidakHadir);

        $this->actingAs($this->ortu)->post(route('sesi.konfirmasi', $pertama))->assertRedirect(route('pesanan.show', $this->pesanan).'#sesi-'.$pertama->id_sesi);

        $pertama->refresh();
        $this->assertSame(StatusKonfirmasiSesi::Dikonfirmasi, $pertama->status_konfirmasi);
        $this->assertSame('ortu', $pertama->dikonfirmasi_oleh);
        $this->assertSame(StatusPesanan::Dibayar, $this->pesanan->refresh()->status);
        Notification::assertSentTo($this->tutor->akun, Pemberitahuan::class, fn (Pemberitahuan $n) => $n->jenis === JenisNotifikasi::SesiDikonfirmasi);

        // "Siswa tidak hadir" still counts as held once confirmed.
        $this->actingAs($this->ortu)->post(route('sesi.konfirmasi', $kedua));
        $this->assertSame(StatusPesanan::Selesai, $this->pesanan->refresh()->status);
        $this->assertSame('170000.00', app(SaldoTutor::class)->satu($this->tutor->id_tutor)['saldo']);
    }

    public function test_only_the_parent_of_the_order_can_confirm_or_dispute(): void
    {
        $sesi = $this->catat();

        $this->actingAs(AkunPengguna::factory()->ortu()->create())->post(route('sesi.konfirmasi', $sesi))->assertSessionHas('galat');
        $this->actingAs(AkunPengguna::factory()->ortu()->create())->post(route('sesi.sanggah', $sesi), ['alasan' => 'Bukan pesanan saya tetapi coba.'])->assertSessionHas('galat');
        $this->actingAs($this->tutor->akun)->post(route('sesi.konfirmasi', $sesi))->assertForbidden();
        $this->actingAs($this->admin)->post(route('sesi.konfirmasi', $sesi))->assertForbidden();

        $this->assertSame(StatusKonfirmasiSesi::MenungguKonfirmasi, $sesi->refresh()->status_konfirmasi);
        $this->assertSame(0, Keluhan::query()->count());
    }

    public function test_a_session_not_logged_yet_cannot_be_confirmed(): void
    {
        $belum = $this->pesanan->sesi()->orderBy('waktu')->firstOrFail();

        $this->actingAs($this->ortu)->post(route('sesi.konfirmasi', $belum))->assertSessionHas('galat');
        $this->assertNull($belum->refresh()->status_konfirmasi);
    }

    public function test_disputing_needs_a_reason_and_opens_a_complaint_for_the_admin(): void
    {
        $sesi = $this->catat();

        $this->actingAs($this->ortu)->post(route('sesi.sanggah', $sesi), ['alasan' => '', 'untuk_sesi' => $sesi->id_sesi])->assertSessionHasErrorsIn('sanggahan', 'alasan');
        $this->assertSame(StatusKonfirmasiSesi::MenungguKonfirmasi, $sesi->refresh()->status_konfirmasi);

        $this->actingAs($this->ortu)->post(route('sesi.sanggah', $sesi), ['alasan' => 'Tutor tidak datang dan tidak memberi kabar.']);

        $sesi->refresh();
        $this->assertSame(StatusKonfirmasiSesi::Disanggah, $sesi->status_konfirmasi);
        $this->assertSame('Tutor tidak datang dan tidak memberi kabar.', $sesi->alasan_sanggahan);

        $keluhan = Keluhan::query()->sole();
        $this->assertSame($sesi->id_sesi, $keluhan->id_sesi);
        $this->assertSame(JenisKeluhan::SesiDisanggah, $keluhan->jenis);
        $this->assertSame($this->ortu->id_akun, $keluhan->id_akun);
        Notification::assertSentTo($this->admin, Pemberitahuan::class, fn (Pemberitahuan $n) => $n->jenis === JenisNotifikasi::SesiDisanggah && $n->tautan === route('admin.keluhan.show', $keluhan));
        Notification::assertSentTo($this->tutor->akun, Pemberitahuan::class, fn (Pemberitahuan $n) => $n->jenis === JenisNotifikasi::SesiDisanggah);

        // Held while disputed.
        $saldo = app(SaldoTutor::class)->satu($this->tutor->id_tutor);
        $this->assertSame(['0.00', '170000.00'], [$saldo['saldo'], $saldo['ditahan']]);
    }

    public function test_silence_confirms_automatically_after_48_hours_and_not_before(): void
    {
        $sesi = $this->catat();

        $this->travel(47)->hours();
        $this->artisan('edulink:konfirmasi-otomatis')->assertSuccessful();
        $this->assertSame(StatusKonfirmasiSesi::MenungguKonfirmasi, $sesi->refresh()->status_konfirmasi);

        $this->travel(61)->minutes();
        $this->artisan('edulink:konfirmasi-otomatis')->assertSuccessful();
        $this->artisan('edulink:konfirmasi-otomatis')->assertSuccessful();

        $sesi->refresh();
        $this->assertSame(StatusKonfirmasiSesi::DikonfirmasiOtomatis, $sesi->status_konfirmasi);
        $this->assertSame('sistem', $sesi->dikonfirmasi_oleh);
        Notification::assertSentToTimes($this->tutor->akun, Pemberitahuan::class, 1 + Notification::sent($this->tutor->akun, Pemberitahuan::class)->reject(fn (Pemberitahuan $n) => $n->jenis === JenisNotifikasi::SesiDikonfirmasi)->count());
        Notification::assertSentTo($this->ortu, Pemberitahuan::class, fn (Pemberitahuan $n) => $n->jenis === JenisNotifikasi::SesiDikonfirmasi);

        // Too late to dispute now.
        $this->actingAs($this->ortu)->post(route('sesi.sanggah', $sesi), ['alasan' => 'Ternyata tutor terlambat.'])->assertSessionHas('galat');
    }

    public function test_the_demo_shortcut_confirms_now_only_in_demo_mode(): void
    {
        $sesi = $this->catat();

        config(['edulink.mode_demo' => false]);
        $this->artisan('edulink:konfirmasi-otomatis', ['--setelah' => 0])->assertFailed();
        $this->assertSame(StatusKonfirmasiSesi::MenungguKonfirmasi, $sesi->refresh()->status_konfirmasi);

        config(['edulink.mode_demo' => true]);
        $this->artisan('edulink:konfirmasi-otomatis', ['--setelah' => 0])->expectsOutputToContain('1 sesi dikonfirmasi otomatis')->assertSuccessful();
        $this->assertSame(StatusKonfirmasiSesi::DikonfirmasiOtomatis, $sesi->refresh()->status_konfirmasi);
    }

    public function test_admin_rules_a_dispute_valid_and_the_tutor_is_paid(): void
    {
        $sesi = $this->catat();
        $this->actingAs($this->ortu)->post(route('sesi.sanggah', $sesi), ['alasan' => 'Materinya tidak sesuai catatan.']);
        $keluhan = Keluhan::query()->sole();

        $this->actingAs($this->admin)->get(route('admin.keluhan.show', $keluhan))->assertSeeText('Putuskan sanggahan sesi')->assertSeeText('Materinya tidak sesuai catatan.');
        $this->actingAs($this->admin)->post(route('admin.keluhan.putusan', $keluhan), ['keputusan' => 'sah'])->assertSessionHasErrors('alasan');

        $this->actingAs($this->admin)->post(route('admin.keluhan.putusan', $keluhan), ['keputusan' => 'sah', 'alasan' => 'Rekaman meeting menunjukkan sesi berjalan.']);

        $sesi->refresh();
        $this->assertSame(StatusKonfirmasiSesi::Dikonfirmasi, $sesi->status_konfirmasi);
        $this->assertSame('admin', $sesi->dikonfirmasi_oleh);
        $this->assertStringContainsString('Rekaman meeting', $sesi->keputusan_admin);
        $this->assertSame(StatusKeluhan::Selesai, $keluhan->refresh()->status);
        $this->assertSame('85000.00', app(SaldoTutor::class)->satu($this->tutor->id_tutor)['saldo']);
        Notification::assertSentTo($this->ortu, Pemberitahuan::class, fn (Pemberitahuan $n) => $n->jenis === JenisNotifikasi::SanggahanDiputuskan);
        Notification::assertSentTo($this->tutor->akun, Pemberitahuan::class, fn (Pemberitahuan $n) => $n->jenis === JenisNotifikasi::SanggahanDiputuskan);
    }

    public function test_admin_rules_a_dispute_not_valid_and_the_parent_is_refunded(): void
    {
        $tidakSah = $this->catat(0);
        $this->actingAs($this->ortu)->post(route('sesi.sanggah', $tidakSah), ['alasan' => 'Tutor tidak datang sama sekali.']);
        $keluhan = Keluhan::query()->sole();

        $this->actingAs($this->admin)->post(route('admin.keluhan.putusan', $keluhan), ['keputusan' => 'tidak_sah', 'alasan' => 'Tutor mengakui tidak datang.']);

        $tidakSah->refresh();
        $this->assertSame(StatusKonfirmasiSesi::TidakSah, $tidakSah->status_konfirmasi);
        $pengembalian = $this->pesanan->pengembalian()->sole();
        $this->assertSame('100000.00', $pengembalian->nominal);
        $this->assertSame($pengembalian->id_pengembalian, $tidakSah->id_pengembalian);
        $this->assertSame('0.00', app(SaldoTutor::class)->satu($this->tutor->id_tutor)['saldo']);

        // A ruled session cannot be ruled again.
        $this->actingAs($this->admin)->post(route('admin.keluhan.putusan', $keluhan), ['keputusan' => 'sah', 'alasan' => 'Berubah pikiran.'])->assertSessionHas('galat');

        // The other session confirmed: every session is settled, so the order finishes.
        $kedua = $this->catat(1);
        $this->actingAs($this->ortu)->post(route('sesi.konfirmasi', $kedua));
        $this->assertSame(StatusPesanan::Selesai, $this->pesanan->refresh()->status);
        $this->assertSame('85000.00', app(SaldoTutor::class)->satu($this->tutor->id_tutor)['saldo']);
    }

    public function test_commission_is_recognised_in_the_month_the_session_is_confirmed(): void
    {
        $this->travelTo(Carbon::parse('2026-10-30 10:00'));
        $tutor = Tutor::factory()->siapDipesan(['tarif' => '100000.00'])->create()->load('profil', 'mataPelajaran', 'jadwal');
        $pesanan = $this->pesananDibayar(null, $tutor, ['jumlah_pertemuan' => 2]);
        $this->catatDanKonfirmasi($pesanan, 1, konfirmasi: false);

        $this->travelTo(Carbon::parse('2026-11-02 10:00'));
        $this->artisan('edulink:konfirmasi-otomatis');

        $oktober = app(LaporanRingkasan::class)->untuk('2026-10');
        $november = app(LaporanRingkasan::class)->untuk('2026-11');

        // Paid in October (Rp200.000 transaction), but the first session's Rp15.000 commission is recognised in November.
        $this->assertSame('200000.00', $oktober['total_transaksi']);
        $this->assertSame('0.00', $oktober['pendapatan_komisi']);
        $this->assertSame('15000.00', $november['pendapatan_komisi']);
        // Held right now: Rp15.000 from October's second session plus Rp30.000 from the setUp order's two sessions.
        $this->assertSame('45000.00', $november['komisi_ditahan']);

        $csv = $this->actingAs($this->admin)->get(route('admin.laporan.csv', ['periode' => '2026-11']))->getContent();
        $this->assertStringContainsString('KOMISI PLATFORM DIAKUI', $csv);
        $this->assertMatchesRegularExpression('/;sistem;15;100000;15000\r\n/', $csv);
        $this->assertMatchesRegularExpression('/Komisi ditahan[^\r\n]*;45000\r\n/', $csv);
    }
}
