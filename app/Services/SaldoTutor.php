<?php

namespace App\Services;

use App\Enums\StatusKonfirmasiSesi;
use App\Enums\StatusPencairan;
use App\Models\PencairanDanaTutor;
use App\Models\Pesanan;
use App\Models\SesiPembelajaran;
use App\Support\PembagianSesi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * A tutor's money, worked out per session of every paid order (an order
 * with a commission row was paid; that includes paid orders later cancelled):
 *
 *   bagian tutor per sesi = nilai per sesi - komisi per sesi   (see PembagianSesi)
 *   saldo tersedia = bagian of sessions dikonfirmasi or dikonfirmasi_otomatis
 *                    - payouts disetujui or dibayar
 *   dana ditahan   = bagian of sessions not settled yet: not logged,
 *                    menunggu_konfirmasi, or disanggah
 *
 * Sessions ruled tidak_sah, and sessions refunded when the admin cancelled
 * the order, earn the tutor nothing. Payout requests still waiting are not
 * deducted yet.
 */
class SaldoTutor
{
    /**
     * @param  list<string>  $idTutor
     * @return array<string, array{pendapatan: string, komisi: string, bagian: string, ditahan: string, dicairkan: string, saldo: string}>
     */
    public function untuk(array $idTutor): array
    {
        $kosong = ['pendapatan' => '0.00', 'komisi' => '0.00', 'bagian' => '0.00', 'ditahan' => '0.00'];
        $jumlah = array_fill_keys($idTutor, $kosong);

        foreach ($this->pesananDibayar(Pesanan::query()->whereIn('id_tutor', $idTutor)) as $pesanan) {
            foreach (self::porsiSesi($pesanan) as $porsi) {
                $baris = &$jumlah[$pesanan->id_tutor];

                if ($porsi['keadaan'] === 'tersedia') {
                    $baris['pendapatan'] = bcadd($baris['pendapatan'], $porsi['nilai'], 2);
                    $baris['komisi'] = bcadd($baris['komisi'], $porsi['komisi'], 2);
                    $baris['bagian'] = bcadd($baris['bagian'], $porsi['bagian'], 2);
                } elseif ($porsi['keadaan'] === 'ditahan') {
                    $baris['ditahan'] = bcadd($baris['ditahan'], $porsi['bagian'], 2);
                }

                unset($baris);
            }
        }

        $dicairkan = PencairanDanaTutor::query()
            ->whereIn('id_tutor', $idTutor)
            ->whereIn('status', [StatusPencairan::Disetujui, StatusPencairan::Dibayar])
            ->groupBy('id_tutor')
            ->selectRaw('id_tutor, SUM(nominal) as total')
            ->pluck('total', 'id_tutor');

        $hasil = [];

        foreach ($idTutor as $id) {
            $keluar = bcadd((string) ($dicairkan[$id] ?? 0), '0', 2);
            $hasil[$id] = $jumlah[$id] + ['dicairkan' => $keluar, 'saldo' => bcsub($jumlah[$id]['bagian'], $keluar, 2)];
        }

        return $hasil;
    }

    /**
     * @return array{pendapatan: string, komisi: string, bagian: string, ditahan: string, dicairkan: string, saldo: string}
     */
    public function satu(string $idTutor): array
    {
        return $this->untuk([$idTutor])[$idTutor];
    }

    /**
     * Per paid order, newest first: session counts by state and the money in each state.
     *
     * @return Collection<int, array{pesanan: Pesanan, total: string, tersedia: string, ditahan: string, dikembalikan: string, sesi_tersedia: int, sesi_ditahan: int, sesi_tidak_sah: int, sesi_dikembalikan: int}>
     */
    public function rincian(string $idTutor): Collection
    {
        return $this->pesananDibayar(Pesanan::query()->where('id_tutor', $idTutor)->with(['siswa', 'mapel'])->latest())
            ->map(function (Pesanan $pesanan): array {
                $porsi = collect(self::porsiSesi($pesanan));
                $jumlah = fn (string $keadaan, string $kolom): string => $porsi->where('keadaan', $keadaan)
                    ->reduce(fn (string $total, array $satu): string => bcadd($total, $satu[$kolom], 2), '0.00');

                return [
                    'pesanan' => $pesanan,
                    'total' => bcadd((string) $pesanan->total_biaya, '0', 2),
                    'tersedia' => $jumlah('tersedia', 'bagian'),
                    'ditahan' => $jumlah('ditahan', 'bagian'),
                    'dikembalikan' => bcadd($jumlah('dikembalikan', 'nilai'), $jumlah('tidak_sah', 'nilai'), 2),
                    'sesi_tersedia' => $porsi->where('keadaan', 'tersedia')->count(),
                    'sesi_ditahan' => $porsi->where('keadaan', 'ditahan')->count(),
                    'sesi_tidak_sah' => $porsi->where('keadaan', 'tidak_sah')->count(),
                    'sesi_dikembalikan' => $porsi->where('keadaan', 'dikembalikan')->count(),
                ];
            });
    }

    /**
     * Every session of a paid order with its value split and where its money
     * stands: tersedia (tutor's), ditahan (not settled), tidak_sah or
     * dikembalikan (back to the parent). Needs sesi and komisi loaded.
     *
     * @return list<array{sesi: SesiPembelajaran, nilai: string, komisi: string, bagian: string, keadaan: string}>
     */
    public static function porsiSesi(Pesanan $pesanan): array
    {
        $pembagian = PembagianSesi::untuk($pesanan, $pesanan->sesi, $pesanan->komisi?->nominal);

        return $pesanan->sesi->sortBy('id_sesi', SORT_NATURAL)->values()->map(fn (SesiPembelajaran $sesi): array => $pembagian[$sesi->id_sesi] + [
            'sesi' => $sesi,
            'keadaan' => match (true) {
                $sesi->status_konfirmasi?->menjadiHakTutor() ?? false => 'tersedia',
                $sesi->status_konfirmasi === StatusKonfirmasiSesi::TidakSah => 'tidak_sah',
                $sesi->id_pengembalian !== null => 'dikembalikan',
                default => 'ditahan',
            },
        ])->all();
    }

    /**
     * Orders that were paid: they have a commission row from the moment of payment.
     *
     * @param  Builder<Pesanan>  $query
     * @return Collection<int, Pesanan>
     */
    private function pesananDibayar(Builder $query): Collection
    {
        return $query->whereHas('komisi')->with(['komisi', 'sesi'])->get();
    }
}
