<?php

namespace Tests\Feature\Database;

use App\Enums\MetodePembayaran;
use App\Enums\StatusPembayaran;
use App\Models\KomisiBiayaLayanan;
use App\Models\Tutor;
use App\Services\LaporanRingkasan;
use App\Services\PemrosesPembayaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\MembuatPesanan;
use Tests\TestCase;

class ViewLaporanTest extends TestCase
{
    use MembuatPesanan, RefreshDatabase;

    public function test_rating_view_skips_hidden_reviews_and_matches_the_rating_scope(): void
    {
        $tutor = $this->tutorSiapDipesan();
        $tutor->jadwal()->create(['hari' => 'sabtu', 'jam' => '08:00:00', 'status' => 'tersedia']);
        $tutor->jadwal()->create(['hari' => 'minggu', 'jam' => '08:00:00', 'status' => 'tersedia']);
        $jadwal = $tutor->jadwal()->orderBy('id_jadwal')->pluck('id_jadwal');
        $tanpaUlasan = $this->tutorSiapDipesan();

        $this->pesananSelesai(tutor: $tutor, ubah: ['id_jadwal' => $jadwal[0]])->ulasan()->create(['nilai' => 5]);
        $this->pesananSelesai(tutor: $tutor, ubah: ['id_jadwal' => $jadwal[1]])->ulasan()->create(['nilai' => 3]);
        $this->pesananSelesai(tutor: $tutor, ubah: ['id_jadwal' => $jadwal[2]])->ulasan()->create(['nilai' => 1])
            ->forceFill(['ditampilkan' => false, 'alasan_moderasi' => 'Berisi nomor telepon.'])->save();

        $view = DB::table('v_rating_tutor')->get()->keyBy('id_tutor');

        $this->assertEqualsWithDelta(4.0, (float) $view[$tutor->id_tutor]->rata_rating, 0.0001);
        $this->assertSame(2, (int) $view[$tutor->id_tutor]->jumlah_ulasan);
        $this->assertNull($view[$tanpaUlasan->id_tutor]->rata_rating);
        $this->assertSame(0, (int) $view[$tanpaUlasan->id_tutor]->jumlah_ulasan);

        $scope = Tutor::query()->denganRating()->get()->keyBy('id_tutor');

        $this->assertEqualsCanonicalizing($scope->keys()->all(), $view->keys()->all());
        foreach ($scope as $id => $satu) {
            $this->assertEqualsWithDelta($satu->rata_rating, $view[$id]->rata_rating, 0.0001, "rata_rating {$id}");
            $this->assertSame((int) $satu->jumlah_ulasan, (int) $view[$id]->jumlah_ulasan, "jumlah_ulasan {$id}");
        }
    }

    public function test_payment_success_rate_counts_every_attempt(): void
    {
        $this->assertNull(DB::table('v_tingkat_keberhasilan_pembayaran')->first()->persen_berhasil);

        $this->pesananDibayar();
        $this->pesananDibayar();
        $this->pesananDibayar();
        $this->percobaanBayar(StatusPembayaran::Gagal);

        $baris = DB::table('v_tingkat_keberhasilan_pembayaran')->first();

        $this->assertSame(4, (int) $baris->total_percobaan);
        $this->assertSame(3, (int) $baris->jumlah_berhasil);
        $this->assertSame(1, (int) $baris->jumlah_gagal);
        $this->assertSame(0, (int) $baris->jumlah_pending);
        $this->assertSame('75.00', number_format((float) $baris->persen_berhasil, 2, '.', ''));

        $this->percobaanBayar(null);
        $baris = DB::table('v_tingkat_keberhasilan_pembayaran')->first();

        $this->assertSame(1, (int) $baris->jumlah_pending);
        $this->assertSame('60.00', number_format((float) $baris->persen_berhasil, 2, '.', ''));
    }

    public function test_daily_transactions_only_count_successful_payments_and_match_the_admin_summary(): void
    {
        // October: one paid order, outside the period checked below.
        $this->travelTo(Carbon::parse('2026-10-30 10:00'));
        $this->pesananDibayar();

        // November 3: two paid orders, one failed attempt, one still pending.
        $this->travelTo(Carbon::parse('2026-11-03 09:00'));
        $this->pesananDibayar();
        $this->pesananDibayar(ubah: ['jumlah_pertemuan' => 2]);
        $this->percobaanBayar(StatusPembayaran::Gagal);
        $this->percobaanBayar(null);

        // November 10: one more paid order.
        $this->travelTo(Carbon::parse('2026-11-10 09:00'));
        $dibayar = $this->pesananDibayar(ubah: ['durasi' => 90]);

        $harian = DB::table('v_laporan_transaksi_harian')->orderBy('tanggal')->get()->keyBy('tanggal');

        $this->assertSame(['2026-10-30', '2026-11-03', '2026-11-10'], $harian->keys()->all());
        $this->assertSame(2, (int) $harian['2026-11-03']->jumlah_pembayaran);
        $this->assertSame(1, (int) $harian['2026-11-10']->jumlah_pembayaran);
        $this->assertSame(
            bcadd((string) $dibayar->komisi->nominal, '0', 2),
            bcadd((string) $harian['2026-11-10']->total_komisi, '0', 2),
        );

        $november = $harian->where('tanggal', '>=', '2026-11-01');
        $ringkasan = app(LaporanRingkasan::class)->untuk('2026-11');

        $this->assertSame($ringkasan['jumlah_transaksi'], (int) $november->sum('jumlah_pembayaran'));
        $this->assertSame(
            $ringkasan['total_transaksi'],
            $november->reduce(fn (string $total, object $baris): string => bcadd($total, (string) $baris->total_pembayaran, 2), '0.00'),
        );
        // Commission is fixed when the order is paid, so November's rows hold
        // the commission rows created in November.
        $this->assertSame(
            bcadd((string) KomisiBiayaLayanan::query()->whereBetween('created_at', ['2026-11-01 00:00:00', '2026-11-30 23:59:59'])->sum('nominal'), '0', 2),
            $november->reduce(fn (string $total, object $baris): string => bcadd($total, (string) $baris->total_komisi, 2), '0.00'),
        );
    }

    /**
     * One payment attempt for a new order: failed, or left pending when $hasil is null.
     */
    private function percobaanBayar(?StatusPembayaran $hasil): void
    {
        $pesanan = $this->pesananMenungguPembayaran();
        $pemroses = app(PemrosesPembayaran::class);
        $pembayaran = $pemroses->mulai($pesanan, MetodePembayaran::Qris);

        if ($hasil !== null) {
            $pemroses->terimaNotifikasi($pembayaran->id_pembayaran, $pesanan->id_pesanan, $hasil);
        }
    }
}
