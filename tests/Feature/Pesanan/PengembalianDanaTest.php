<?php

namespace Tests\Feature\Pesanan;

use App\Enums\JenisNotifikasi;
use App\Enums\StatusPengembalian;
use App\Models\AkunPengguna;
use App\Models\Pengaturan;
use App\Models\PengembalianDana;
use App\Models\Pesanan;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Notifications\Pemberitahuan;
use App\Services\KonfirmasiSesi;
use App\Services\LaporanRingkasan;
use App\Services\SaldoTutor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

class PengembalianDanaTest extends TestCase
{
    use MembuatPesanan, RefreshDatabase;

    private AkunPengguna $admin;

    private Tutor $tutor;

    private AkunPengguna $ortu;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->admin = AkunPengguna::factory()->admin()->create();
        $this->tutor = Tutor::factory()->siapDipesan(['tarif' => '80000.00'])->create()->load('profil', 'mataPelajaran', 'jadwal');
    }

    /**
     * A paid order of $pertemuan one-hour sessions (Rp80.000 each, 15% commission),
     * with $dikonfirmasi of them held and confirmed, then cancelled by the admin.
     */
    private function pesananDibatalkan(int $pertemuan = 4, int $dikonfirmasi = 1): Pesanan
    {
        $siswa = Siswa::factory()->create();
        $this->ortu = $siswa->wali;
        $pesanan = $this->pesananDibayar($siswa, $this->tutor, ['jumlah_pertemuan' => $pertemuan]);
        $this->catatDanKonfirmasi($pesanan, $dikonfirmasi);

        $this->actingAs($this->admin)->post(route('admin.pesanan.batal', $pesanan), ['alasan' => 'Tutor pindah kota.']);

        return $pesanan->refresh();
    }

    public function test_cancelling_a_paid_order_refunds_the_sessions_not_held(): void
    {
        $pesanan = $this->pesananDibatalkan(4, 1);

        $pengembalian = $pesanan->pengembalian()->sole();
        $this->assertSame('240000.00', $pengembalian->nominal);
        $this->assertSame(3, $pengembalian->sesi()->count());
        $this->assertSame(StatusPengembalian::PerluDiproses, $pengembalian->status);
        $this->assertSame('batal', $pesanan->status->value);
        Notification::assertSentTo($this->ortu, Pemberitahuan::class, fn (Pemberitahuan $n) => $n->jenis === JenisNotifikasi::PesananDibatalkan && str_contains($n->pesan, 'Rp240.000'));
    }

    public function test_the_refund_is_the_exact_value_of_the_sessions_it_covers(): void
    {
        // Rp99.999,99 over 3 sessions: Rp33.333 + Rp33.333 + Rp33.333,99. One held, two refunded.
        $this->tutor->profil->update(['tarif' => '33333.33']);

        $pesanan = $this->pesananDibatalkan(3, 1);

        $this->assertSame('99999.99', $pesanan->total_biaya);
        $this->assertSame('66666.99', $pesanan->pengembalian()->sole()->nominal);
    }

    public function test_a_logged_session_keeps_going_through_confirmation_after_a_cancellation(): void
    {
        $siswa = Siswa::factory()->create();
        $this->ortu = $siswa->wali;
        $pesanan = $this->pesananDibayar($siswa, $this->tutor, ['jumlah_pertemuan' => 4]);
        $this->catatDanKonfirmasi($pesanan, 1);
        $this->catatDanKonfirmasi($pesanan, 1, konfirmasi: false);

        $this->actingAs($this->admin)->post(route('admin.pesanan.batal', $pesanan), ['alasan' => 'Tutor pindah kota.']);

        // Only the two sessions not logged are refunded now.
        $this->assertSame('160000.00', $pesanan->pengembalian()->sole()->nominal);

        // The parent disputes the logged one and the admin rules it not valid: it joins the open refund.
        $menunggu = $pesanan->sesi()->where('status_konfirmasi', 'menunggu_konfirmasi')->sole();
        app(KonfirmasiSesi::class)->sanggah($menunggu, $this->ortu, 'Tutor tidak datang sama sekali.');
        app(KonfirmasiSesi::class)->putuskan($menunggu, $this->admin, false, 'Tutor mengakui tidak datang.');

        $this->assertSame('240000.00', $pesanan->pengembalian()->sole()->nominal);
        $this->assertSame('68000.00', app(SaldoTutor::class)->satu($this->tutor->id_tutor)['saldo']);
    }

    public function test_commission_stays_as_charged_and_only_confirmed_sessions_count(): void
    {
        $pesanan = $this->pesananDibatalkan(4, 1);

        // The rate changing later does not touch this order.
        Pengaturan::simpan(Pengaturan::KOMISI_PERSEN, '20');

        $komisi = $pesanan->komisi()->first();
        $this->assertSame('15.00', $komisi->persentase);
        $this->assertSame('48000.00', $komisi->nominal);

        $saldo = app(SaldoTutor::class)->satu($this->tutor->id_tutor);
        $this->assertSame(['80000.00', '12000.00', '68000.00', '0.00'], [$saldo['pendapatan'], $saldo['komisi'], $saldo['saldo'], $saldo['ditahan']]);
    }

    public function test_cancelling_an_unpaid_order_creates_no_refund(): void
    {
        $pesanan = $this->pesananMenungguPembayaran(null, $this->tutor);

        $this->actingAs($this->admin)->post(route('admin.pesanan.batal', $pesanan), ['alasan' => 'Duplikat pesanan.']);

        $this->assertSame(0, PengembalianDana::query()->count());
        $this->assertSame('0.00', app(SaldoTutor::class)->satu($this->tutor->id_tutor)['saldo']);
    }

    public function test_the_summary_report_counts_refunds_and_only_confirmed_commission(): void
    {
        $this->pesananDibatalkan(4, 1);

        $r = app(LaporanRingkasan::class)->untuk(now()->format('Y-m'));

        $this->assertSame('240000.00', $r['total_pengembalian']);
        $this->assertSame(1, $r['jumlah_pengembalian']);
        $this->assertSame('320000.00', $r['total_transaksi']);
        $this->assertSame('12000.00', $r['pendapatan_komisi']);
        $this->assertSame('0.00', $r['komisi_ditahan']);
        $this->actingAs($this->admin)->get(route('admin.beranda'))->assertSeeText('Pengembalian dana')->assertSeeText('Rp240.000');
    }

    public function test_admin_marks_the_refund_done_and_the_parent_is_told(): void
    {
        $pesanan = $this->pesananDibatalkan(4, 1);
        $pengembalian = $pesanan->pengembalian()->sole();

        $this->actingAs($this->admin)->get(route('admin.pengembalian.index', ['status' => 'perlu_diproses']))->assertSeeText($pengembalian->id_pengembalian);

        $this->actingAs($this->admin)->post(route('admin.pengembalian.selesai', $pengembalian), ['catatan' => ''])->assertSessionHasErrors('catatan');
        $this->actingAs($this->admin)->post(route('admin.pengembalian.selesai', $pengembalian), ['catatan' => 'Ditransfer ke BCA a.n. Siti tanggal 21 Nov.']);

        $pengembalian->refresh();
        $this->assertSame(StatusPengembalian::Selesai, $pengembalian->status);
        $this->assertSame($this->admin->id_akun, $pengembalian->diproses_oleh);
        Notification::assertSentTo($this->ortu, Pemberitahuan::class, fn (Pemberitahuan $n) => $n->jenis === JenisNotifikasi::PengembalianDana);

        $this->actingAs($this->ortu)->get(route('pesanan.show', $pesanan))->assertSeeText('Sudah dikembalikan')->assertSeeText('Ditransfer ke BCA');
        $this->actingAs($this->ortu)->get(route('pembayaran.index'))->assertSeeText('Pengembalian dana')->assertSeeText('Rp240.000');

        $this->actingAs($this->admin)->post(route('admin.pengembalian.selesai', $pengembalian), ['catatan' => 'Dua kali.'])->assertSessionHas('galat');
    }

    public function test_only_admins_reach_the_refund_page(): void
    {
        $pesanan = $this->pesananDibatalkan(4, 1);

        $this->actingAs($this->ortu)->get(route('admin.pengembalian.index'))->assertForbidden();
        $this->actingAs($this->ortu)->post(route('admin.pengembalian.selesai', $pesanan->pengembalian()->sole()), ['catatan' => 'Sudah saya terima.'])->assertForbidden();
    }
}
