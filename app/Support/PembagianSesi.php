<?php

namespace App\Support;

use App\Models\Pesanan;
use App\Models\SesiPembelajaran;
use Illuminate\Support\Collection;

/**
 * Splits an order's total and its commission over its sessions.
 *
 *   nilai per sesi  = total_biaya / jumlah_pertemuan, rounded down to whole rupiah
 *   komisi per sesi = commission charged at payment / jumlah_pertemuan, same rounding
 *   bagian tutor    = nilai - komisi
 *
 * The last session (by creation order, so a reschedule never moves the
 * remainder) takes what rounding left over, so the sessions always add up
 * to exactly the order total and the commission charged.
 */
final class PembagianSesi
{
    /**
     * @param  Collection<int, SesiPembelajaran>  $sesi  every session of the order
     * @return array<string, array{nilai: string, komisi: string, bagian: string}> keyed by id_sesi
     */
    public static function untuk(Pesanan $pesanan, Collection $sesi, string|int|float|null $komisiTotal): array
    {
        $urut = $sesi->sortBy('id_sesi', SORT_NATURAL)->values();
        $jumlah = max(1, $urut->count());

        $total = bcadd((string) $pesanan->total_biaya, '0', 2);
        $komisi = bcadd((string) ($komisiTotal ?? 0), '0', 2);

        $nilaiDasar = bcadd(bcdiv($total, (string) $jumlah, 0), '0', 2);
        $komisiDasar = bcadd(bcdiv($komisi, (string) $jumlah, 0), '0', 2);

        $hasil = [];

        foreach ($urut as $indeks => $satu) {
            $terakhir = $indeks === $jumlah - 1;
            $nilai = $terakhir ? bcsub($total, bcmul($nilaiDasar, (string) ($jumlah - 1), 2), 2) : $nilaiDasar;
            $potongan = $terakhir ? bcsub($komisi, bcmul($komisiDasar, (string) ($jumlah - 1), 2), 2) : $komisiDasar;

            $hasil[$satu->id_sesi] = ['nilai' => $nilai, 'komisi' => $potongan, 'bagian' => bcsub($nilai, $potongan, 2)];
        }

        return $hasil;
    }
}
