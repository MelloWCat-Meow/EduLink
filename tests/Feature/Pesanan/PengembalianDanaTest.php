<?php

namespace Tests\Feature\Pesanan;

use App\Enums\JenisNotifikasi;
use App\Enums\Kehadiran;
use App\Enums\StatusPengembalian;
use App\Models\AkunPengguna;
use App\Models\Pengaturan;
use App\Models\PengembalianDana;
use App\Models\Pesanan;
use App\Models\Siswa;
use App\Models\Tutor;
use App\Notifications\Pemberitahuan;
use App\Services\AlurPesanan;
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
     * A paid order of $pertemuan one-hour sessions, with $tercatat of them already held.
     */
    private function pesananDibatalkan(int $pertemuan = 4, int $tercatat = 1): Pesanan
    {
        $siswa = Siswa::factory()->create();
        $this->ortu = $siswa->wali;
        $pesanan = $this->pesananDibayar($siswa, $this->tutor, ['jumlah_pertemuan' => $pertemuan]);

        foreach ($pesanan->sesi()->orderBy('waktu')->take($tercatat)->get() as $sesi) {
            app(AlurPesanan::class)->catatKehadiran($sesi, Kehadiran::Hadir, periksaWaktu: false);
        }

        $this->actingAs($this->admin)->post(route('admin.pesanan.batal', $pesanan), ['alasan' => 'Tutor pindah kota.']);

        return $pesanan->refresh();
    }

    public function test_cancelling_a_paid_order_refunds_the_sessions_not_held(): void
    {
        $pesanan = $this->pesananDibatalkan(4, 1);

        $pengembalian = $pesanan->pengembalianDana;
        $this->assertSame('240000.00', $pengembalian->nominal);
        $this->assertSame(1, $pengembalian->sesi_terlaksana);
        $this->assertSame(StatusPengembalian::PerluDiproses, $pengembalian->status);
        $this->assertSame('batal', $pesanan->status->value);
        Notification::assertSentTo($this->ortu, Pemberitahuan::class, fn (Pemberitahuan $n) => $n->jenis === JenisNotifikasi::PesananDibatalkan && str_contains($n->pesan, 'Rp240.000'));
    }

    public function test_the_refund_is_rounded_to_whole_rupiah(): void
    {
        $this->tutor->profil->update(['tarif' => '100000.00']);

        $pesanan = $this->pesananDibatalkan(3, 1);

        // 300.000 x 2/3 = 200.000 exactly.
        $this->assertSame('200000.00', $pesanan->pengembalianDana->nominal);

        // A total that does not divide evenly rounds half up to whole rupiah (100.000 x 2/3).
        $this->assertSame('66667.00', (fn () => self::bulatkanRupiah(bcdiv('200000', '3', 4)))->call(app(AlurPesanan::class)));
    }

    public function test_commission_and_tutor_income_count_only_the_sessions_held_at_the_rate_charged_when_paid(): void
    {
        $siswa = Siswa::factory()->create();
        $pesanan = $this->pesananDibayar($siswa, $this->tutor, ['jumlah_pertemuan' => 4]);
        app(AlurPesanan::class)->catatKehadiran($pesanan->sesi()->orderBy('waktu')->first(), Kehadiran::Hadir, periksaWaktu: false);

        // The rate changes after payment; the cancelled order keeps 15%.
        Pengaturan::simpan(Pengaturan::KOMISI_PERSEN, '20');
        $this->actingAs($this->admin)->post(route('admin.pesanan.batal', $pesanan), ['alasan' => 'Tutor sakit lama.']);

        $komisi = $pesanan->komisi()->first();
        $this->assertSame('15.00', $komisi->persentase);
        $this->assertSame('12000.00', $komisi->nominal);
        $this->assertSame('48000.00', $komisi->nominal_sebelum_pengembalian);

        $saldo = app(SaldoTutor::class)->satu($this->tutor->id_tutor);
        $this->assertSame('80000.00', $saldo['pendapatan']);
        $this->assertSame('12000.00', $saldo['komisi']);
        $this->assertSame('68000.00', $saldo['saldo']);

        $baris = app(SaldoTutor::class)->rincian($this->tutor->id_tutor)->sole();
        $this->assertSame(['320000.00', '240000.00', '12000.00', '68000.00'], [$baris['total'], $baris['pengembalian'], $baris['komisi'], $baris['bersih']]);
    }

    public function test_cancelling_an_unpaid_order_creates_no_refund(): void
    {
        $pesanan = $this->pesananMenungguPembayaran(null, $this->tutor);

        $this->actingAs($this->admin)->post(route('admin.pesanan.batal', $pesanan), ['alasan' => 'Duplikat pesanan.']);

        $this->assertSame(0, PengembalianDana::query()->count());
        $this->assertSame('0.00', app(SaldoTutor::class)->satu($this->tutor->id_tutor)['saldo']);
    }

    public function test_the_summary_report_counts_refunds_and_the_lowered_commission(): void
    {
        $this->pesananDibatalkan(4, 1);

        $r = app(LaporanRingkasan::class)->untuk(now()->format('Y-m'));

        $this->assertSame('240000.00', $r['total_pengembalian']);
        $this->assertSame(1, $r['jumlah_pengembalian']);
        $this->assertSame('320000.00', $r['total_transaksi']);
        $this->assertSame('12000.00', $r['pendapatan_komisi']);
        $this->actingAs($this->admin)->get(route('admin.beranda'))->assertSeeText('Pengembalian dana')->assertSeeText('Rp240.000');
    }

    public function test_admin_marks_the_refund_done_and_the_parent_is_told(): void
    {
        $pesanan = $this->pesananDibatalkan(4, 1);
        $pengembalian = $pesanan->pengembalianDana;

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
        $this->actingAs($this->ortu)->post(route('admin.pengembalian.selesai', $pesanan->pengembalianDana), ['catatan' => 'Sudah saya terima.'])->assertForbidden();
    }
}
