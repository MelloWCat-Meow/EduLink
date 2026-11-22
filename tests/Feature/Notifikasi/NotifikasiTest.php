<?php

namespace Tests\Feature\Notifikasi;

use App\Enums\JenisNotifikasi;
use App\Enums\Kehadiran;
use App\Enums\StatusPencairan;
use App\Models\AkunPengguna;
use App\Models\Keluhan;
use App\Models\PencairanDanaTutor;
use App\Models\Pesanan;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Notifications\Pemberitahuan;
use App\Services\AlurPesanan;
use App\Services\PencairanDana;
use App\Services\PendaftaranTutor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\MembuatPendaftar;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

class NotifikasiTest extends TestCase
{
    use MembuatPendaftar, MembuatPesanan, RefreshDatabase;

    private Tutor $tutor;

    private AkunPengguna $akunTutor;

    private Siswa $siswa;

    private AkunPengguna $ortu;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->tutor = Tutor::factory()->siapDipesan()->denganAkun()->create()->load('profil', 'mataPelajaran', 'jadwal');
        $this->akunTutor = $this->tutor->akun;
        $this->siswa = Siswa::factory()->create();
        $this->ortu = $this->siswa->wali;
    }

    private function alur(): AlurPesanan
    {
        return app(AlurPesanan::class);
    }

    private function pesanan(): Pesanan
    {
        return $this->pesananBaru($this->siswa, $this->tutor);
    }

    private function assertTerkirim(AkunPengguna $akun, JenisNotifikasi $jenis, ?string $tautan = null, int $kali = 1): void
    {
        Notification::assertSentToTimes($akun, Pemberitahuan::class, $kali + Notification::sent($akun, Pemberitahuan::class)->reject(fn (Pemberitahuan $n) => $n->jenis === $jenis)->count());
        Notification::assertSentTo($akun, Pemberitahuan::class, fn (Pemberitahuan $n) => $n->jenis === $jenis && ($tautan === null || $n->tautan === $tautan));
    }

    private function assertTidakTerkirim(AkunPengguna $akun, JenisNotifikasi $jenis): void
    {
        Notification::assertNotSentTo($akun, Pemberitahuan::class, fn (Pemberitahuan $n) => $n->jenis === $jenis);
    }

    public function test_a_new_order_notifies_only_the_tutor(): void
    {
        $pesanan = $this->pesanan();

        $this->assertTerkirim($this->akunTutor, JenisNotifikasi::PesananBaru, route('tutor.pesanan.show', $pesanan));
        $this->assertTidakTerkirim($this->ortu, JenisNotifikasi::PesananBaru);
    }

    public function test_accept_reject_and_tutor_cancel_notify_the_parent(): void
    {
        $diterima = $this->pesanan();
        $this->alur()->terima($diterima, $this->akunTutor);
        $this->assertTerkirim($this->ortu, JenisNotifikasi::PesananDiterima, route('pesanan.show', $diterima));

        $this->alur()->batalkanOlehTutor($diterima, 'Sakit.', $this->akunTutor);
        $this->assertTerkirim($this->ortu, JenisNotifikasi::PesananDibatalkan, route('pesanan.show', $diterima));
        $this->assertTidakTerkirim($this->akunTutor, JenisNotifikasi::PesananDibatalkan);

        $ditolak = $this->pesanan();
        $this->alur()->tolakOlehTutor($ditolak, 'Penuh.', $this->akunTutor);
        Notification::assertSentTo($this->ortu, Pemberitahuan::class, fn (Pemberitahuan $n) => $n->jenis === JenisNotifikasi::PesananDitolak && str_contains($n->pesan, 'Penuh.'));
    }

    public function test_successful_payment_notifies_parent_and_tutor_by_app_and_email(): void
    {
        $pesanan = $this->pesananDibayar($this->siswa, $this->tutor);

        $this->assertTerkirim($this->ortu, JenisNotifikasi::PembayaranBerhasil, route('pesanan.show', $pesanan));
        $this->assertTerkirim($this->akunTutor, JenisNotifikasi::PembayaranBerhasil, route('tutor.pesanan.show', $pesanan));
        Notification::assertSentTo($this->ortu, Pemberitahuan::class, fn (Pemberitahuan $n, array $channels) => $n->jenis === JenisNotifikasi::PembayaranBerhasil && $channels === ['database', 'mail']);
    }

    public function test_a_logged_session_notifies_the_parent(): void
    {
        $pesanan = $this->pesananDibayar($this->siswa, $this->tutor, ['jumlah_pertemuan' => 2]);
        $sesi = $pesanan->sesi()->orderBy('waktu')->first();
        $this->travelTo($sesi->waktu->copy()->addMinutes(5));

        $this->alur()->catatKehadiran($sesi, Kehadiran::Hadir, 'Bab pecahan.', oleh: $this->akunTutor);

        // The parent is asked to confirm or dispute, in the app and by email, with the session id for the inline button.
        Notification::assertSentTo($this->ortu, Pemberitahuan::class, fn (Pemberitahuan $n, array $channels) => $n->jenis === JenisNotifikasi::SesiDicatat
            && str_contains($n->pesan, 'Bab pecahan.') && str_contains($n->pesan, 'sebelum') && $n->tambahan === ['id_sesi' => $sesi->id_sesi] && $channels === ['database', 'mail']);
        $this->assertTidakTerkirim($this->akunTutor, JenisNotifikasi::SesiDicatat);
    }

    public function test_an_expired_order_notifies_parent_and_tutor(): void
    {
        $this->pesanan();
        $this->travel(25)->hours();

        $this->alur()->lepasPenahananKedaluwarsa();

        $this->assertTerkirim($this->ortu, JenisNotifikasi::PesananKedaluwarsa);
        $this->assertTerkirim($this->akunTutor, JenisNotifikasi::PesananKedaluwarsa);
    }

    public function test_day_before_reminder_reaches_both_sides_once(): void
    {
        $pesanan = $this->pesananDibayar($this->siswa, $this->tutor, ['jumlah_pertemuan' => 2]);
        $sesi = $pesanan->sesi()->orderBy('waktu')->first();
        $this->travelTo($sesi->waktu->copy()->subDay()->setTime(7, 0));

        $this->artisan('edulink:pengingat-sesi')->assertSuccessful();
        $this->artisan('edulink:pengingat-sesi')->assertSuccessful();

        $this->assertTerkirim($this->ortu, JenisNotifikasi::PengingatSesi);
        $this->assertTerkirim($this->akunTutor, JenisNotifikasi::PengingatSesi);
        $this->assertNotNull($sesi->refresh()->pengingat_dikirim_pada);
    }

    public function test_reminder_skips_sessions_that_are_not_tomorrow_or_not_paid(): void
    {
        $pesanan = $this->pesananDibayar($this->siswa, $this->tutor);
        $sesi = $pesanan->sesi()->first();
        $this->travelTo($sesi->waktu->copy()->subDays(3));

        $this->artisan('edulink:pengingat-sesi')->assertSuccessful();

        $this->assertTidakTerkirim($this->ortu, JenisNotifikasi::PengingatSesi);
        $this->assertNull($sesi->refresh()->pengingat_dikirim_pada);
    }

    public function test_a_new_review_notifies_the_tutor(): void
    {
        $pesanan = $this->pesananSelesai($this->siswa, $this->tutor);

        $this->actingAs($this->ortu)->post(route('pesanan.ulasan', $pesanan), ['nilai' => 5, 'komentar' => 'Mantap.']);

        $this->assertTerkirim($this->akunTutor, JenisNotifikasi::UlasanBaru, route('tutor.ulasan.index'));
    }

    public function test_complaint_updates_reach_whoever_filed_it(): void
    {
        $pesanan = $this->pesananDibayar($this->siswa, $this->tutor);
        $admin = AkunPengguna::factory()->admin()->create();

        $dariOrtu = new Keluhan(['id_pesanan' => $pesanan->id_pesanan, 'jenis' => 'lainnya', 'deskripsi' => 'Tutor terlambat.']);
        $dariOrtu->id_akun = $this->ortu->id_akun;
        $dariOrtu->save();
        $dariTutor = new Keluhan(['id_pesanan' => $pesanan->id_pesanan, 'jenis' => 'lainnya', 'deskripsi' => 'Siswa pindah.']);
        $dariTutor->id_akun = $this->akunTutor->id_akun;
        $dariTutor->save();

        $this->actingAs($admin)->put(route('admin.keluhan.update', $dariOrtu), ['status' => 'diproses', 'tanggapan_admin' => 'Sedang kami cek.']);
        $this->actingAs($admin)->put(route('admin.keluhan.update', $dariTutor), ['status' => 'selesai', 'tanggapan_admin' => 'Pesanan dibatalkan admin.']);

        $this->assertTerkirim($this->ortu, JenisNotifikasi::KeluhanDiperbarui, route('keluhan.index'));
        $this->assertTerkirim($this->akunTutor, JenisNotifikasi::KeluhanDiperbarui, route('tutor.keluhan.index'));
    }

    public function test_payout_request_notifies_admins_and_decisions_notify_the_tutor(): void
    {
        $adminSatu = AkunPengguna::factory()->admin()->create();
        $adminDua = AkunPengguna::factory()->admin()->create();
        $this->pesananSelesai($this->siswa, $this->tutor);
        $this->tutor->forceFill(['rekening_bank' => 'BRI', 'rekening_nomor' => '1234567890', 'rekening_atas_nama' => 'Tutor'])->save();
        $layanan = app(PencairanDana::class);

        $pencairan = $layanan->ajukan($this->tutor, now()->format('Y-m'), '20000');
        $this->assertTerkirim($adminSatu, JenisNotifikasi::PengajuanPencairanBaru, route('admin.pencairan.index'));
        $this->assertTerkirim($adminDua, JenisNotifikasi::PengajuanPencairanBaru);
        $this->assertTidakTerkirim($this->akunTutor, JenisNotifikasi::PengajuanPencairanBaru);

        $layanan->setujui($pencairan);
        $this->assertTerkirim($this->akunTutor, JenisNotifikasi::PencairanDisetujui, route('tutor.pendapatan.index'));

        $layanan->tandaiDibayar($pencairan);
        $this->assertTerkirim($this->akunTutor, JenisNotifikasi::PencairanDibayar);
        $this->assertSame(StatusPencairan::Dibayar, PencairanDanaTutor::query()->sole()->status);
    }

    public function test_a_sent_application_notifies_every_admin(): void
    {
        Storage::fake('local');
        $admin = AkunPengguna::factory()->admin()->create();
        $pendaftar = $this->pendaftarLengkap();

        app(PendaftaranTutor::class)->kirim($pendaftar);

        $this->assertTerkirim($admin, JenisNotifikasi::PendaftaranTutorBaru, route('admin.verifikasi.show', $pendaftar));
    }

    public function test_internal_tutor_without_an_account_is_simply_skipped(): void
    {
        $tanpaAkun = Tutor::factory()->siapDipesan()->create()->load('profil', 'mataPelajaran', 'jadwal');

        $pesanan = $this->pesananBaru($this->siswa, $tanpaAkun);

        $this->assertSame('menunggu_konfirmasi_tutor', $pesanan->status->value);
        Notification::assertCount(0);
    }
}
