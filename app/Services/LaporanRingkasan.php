<?php

namespace App\Services;

use App\Enums\StatusKeluhan;
use App\Enums\StatusPembayaran;
use App\Enums\StatusPesanan;
use App\Models\Keluhan;
use App\Models\KomisiBiayaLayanan;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Models\Tutor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Admin summary for one calendar month (YYYY-MM, Asia/Jakarta), computed
 * straight from the database:
 *
 * - orders per status and per day: orders created in the month;
 * - successful transactions: payments the gateway confirmed in the month;
 * - commission income: commissions fixed (at payment) in the month;
 * - busiest tutors: orders created in the month that were not rejected or cancelled;
 * - tutors awaiting verification and open complaints: the current queue.
 */
class LaporanRingkasan
{
    /**
     * @return array{
     *     periode: string,
     *     jumlah_per_status: array<string, int>,
     *     jumlah_pesanan: int,
     *     total_transaksi: string,
     *     jumlah_transaksi: int,
     *     pendapatan_komisi: string,
     *     tutor_menunggu_verifikasi: int,
     *     keluhan_terbuka: int,
     *     tutor_terlaris: Collection<int, Tutor>,
     *     pesanan_per_hari: array<int, int>
     * }
     */
    public function untuk(string $periode): array
    {
        $mulai = Carbon::createFromFormat('!Y-m', $periode)->startOfMonth();
        $akhir = $mulai->copy()->endOfMonth();
        $rentang = [$mulai, $akhir];

        $perStatus = Pesanan::query()->whereBetween('created_at', $rentang)
            ->groupBy('status')->selectRaw('status, COUNT(*) as jumlah')->pluck('jumlah', 'status');

        $jumlahPerStatus = [];
        foreach (StatusPesanan::cases() as $status) {
            $jumlahPerStatus[$status->value] = (int) ($perStatus[$status->value] ?? 0);
        }

        $transaksi = Pembayaran::query()
            ->where('status', StatusPembayaran::Berhasil)
            ->whereBetween('diproses_pada', $rentang)
            ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(jumlah), 0) as total')
            ->first();

        $komisi = KomisiBiayaLayanan::query()->whereBetween('created_at', $rentang)->sum('nominal');

        $pesananNyata = fn ($pesanan) => $pesanan
            ->whereBetween('created_at', $rentang)
            ->whereNotIn('status', [StatusPesanan::Ditolak, StatusPesanan::Batal]);

        $tanggalPesanan = Pesanan::query()->whereBetween('created_at', $rentang)->pluck('created_at');
        $perHari = array_fill(1, $mulai->daysInMonth, 0);
        foreach ($tanggalPesanan as $waktu) {
            $perHari[Carbon::parse($waktu)->day]++;
        }

        return [
            'periode' => $periode,
            'jumlah_per_status' => $jumlahPerStatus,
            'jumlah_pesanan' => array_sum($jumlahPerStatus),
            'total_transaksi' => bcadd((string) $transaksi->total, '0', 2),
            'jumlah_transaksi' => (int) $transaksi->jumlah,
            'pendapatan_komisi' => bcadd((string) $komisi, '0', 2),
            'tutor_menunggu_verifikasi' => Tutor::query()->menungguVerifikasi()->count(),
            'keluhan_terbuka' => Keluhan::query()->whereIn('status', [StatusKeluhan::Baru, StatusKeluhan::Diproses])->count(),
            'tutor_terlaris' => Tutor::query()
                ->withCount(['pesanan as jumlah_pesanan' => $pesananNyata])
                ->whereHas('pesanan', $pesananNyata)
                ->orderByDesc('jumlah_pesanan')
                ->orderBy('nama')
                ->limit(5)
                ->get(),
            'pesanan_per_hari' => $perHari,
        ];
    }
}
