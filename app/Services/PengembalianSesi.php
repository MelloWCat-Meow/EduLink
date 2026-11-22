<?php

namespace App\Services;

use App\Enums\StatusPengembalian;
use App\Models\PengembalianDana;
use App\Models\Pesanan;
use App\Models\SesiPembelajaran;
use App\Support\PembagianSesi;
use Illuminate\Support\Collection;

/**
 * Pays sessions back to the parent: sessions ruled not valid, and sessions
 * not held when the admin cancels a paid order. They join the order's open
 * refund (perlu_diproses) if there is one, else a new refund, whose amount is
 * the sum of its sessions' value. Call inside the caller's transaction.
 */
class PengembalianSesi
{
    /**
     * @param  Collection<int, SesiPembelajaran>  $sesi
     */
    public function kembalikan(Pesanan $pesanan, Collection $sesi): ?PengembalianDana
    {
        $sesi = $sesi->whereNull('id_pengembalian');

        if ($sesi->isEmpty()) {
            return null;
        }

        $pengembalian = $pesanan->pengembalian()->where('status', StatusPengembalian::PerluDiproses)->lockForUpdate()->first();

        if ($pengembalian === null) {
            $pengembalian = new PengembalianDana(['id_pesanan' => $pesanan->id_pesanan, 'nominal' => 0, 'status' => StatusPengembalian::PerluDiproses]);
            $pengembalian->save();
        }

        SesiPembelajaran::query()->whereIn('id_sesi', $sesi->pluck('id_sesi'))->update(['id_pengembalian' => $pengembalian->id_pengembalian]);

        $semuaSesi = $pesanan->sesi()->get();
        $pembagian = PembagianSesi::untuk($pesanan, $semuaSesi, $pesanan->komisi()->value('nominal'));
        $nominal = $semuaSesi->where('id_pengembalian', $pengembalian->id_pengembalian)
            ->reduce(fn (string $jumlah, SesiPembelajaran $satu): string => bcadd($jumlah, $pembagian[$satu->id_sesi]['nilai'], 2), '0.00');

        $pengembalian->update(['nominal' => $nominal]);

        return $pengembalian;
    }
}
