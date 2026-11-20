<?php

namespace App\Services;

use App\Enums\StatusPembayaran;
use App\Enums\StatusPencairan;
use App\Enums\StatusPesanan;
use App\Models\KomisiBiayaLayanan;
use App\Models\Pembayaran;
use App\Models\PencairanDanaTutor;
use App\Models\Pesanan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * A tutor's payout balance:
 *   successful payments - platform commission - earlier payouts.
 *
 * Only orders that are paid or finished count; an order the admin cancelled
 * after payment is treated as refunded. Earlier payouts are the approved and
 * paid ones; requests still waiting are not deducted yet.
 */
class SaldoTutor
{
    /**
     * @param  list<string>  $idTutor
     * @return array<string, array{pendapatan: string, komisi: string, dicairkan: string, saldo: string}>
     */
    public function untuk(array $idTutor): array
    {
        $pesananDihitung = fn (Builder $query) => $query->whereIn('status', [StatusPesanan::Dibayar, StatusPesanan::Selesai])->whereIn('id_tutor', $idTutor);

        $pendapatan = Pembayaran::query()
            ->join('pesanan', 'pesanan.id_pesanan', '=', 'pembayaran.id_pesanan')
            ->where('pembayaran.status', StatusPembayaran::Berhasil)
            ->whereIn('pembayaran.id_pesanan', $pesananDihitung(Pesanan::query()->select('id_pesanan')))
            ->groupBy('pesanan.id_tutor')
            ->selectRaw('pesanan.id_tutor, SUM(pembayaran.jumlah) as total')
            ->pluck('total', 'id_tutor');

        $komisi = KomisiBiayaLayanan::query()
            ->join('pesanan', 'pesanan.id_pesanan', '=', 'komisi_biaya_layanan.id_pesanan')
            ->whereIn('komisi_biaya_layanan.id_pesanan', $pesananDihitung(Pesanan::query()->select('id_pesanan')))
            ->groupBy('pesanan.id_tutor')
            ->selectRaw('pesanan.id_tutor, SUM(komisi_biaya_layanan.nominal) as total')
            ->pluck('total', 'id_tutor');

        $dicairkan = PencairanDanaTutor::query()
            ->whereIn('id_tutor', $idTutor)
            ->whereIn('status', [StatusPencairan::Disetujui, StatusPencairan::Dibayar])
            ->groupBy('id_tutor')
            ->selectRaw('id_tutor, SUM(nominal) as total')
            ->pluck('total', 'id_tutor');

        $hasil = [];

        foreach ($idTutor as $id) {
            $masuk = self::uang($pendapatan[$id] ?? 0);
            $potongan = self::uang($komisi[$id] ?? 0);
            $keluar = self::uang($dicairkan[$id] ?? 0);

            $hasil[$id] = [
                'pendapatan' => $masuk,
                'komisi' => $potongan,
                'dicairkan' => $keluar,
                'saldo' => bcsub(bcsub($masuk, $potongan, 2), $keluar, 2),
            ];
        }

        return $hasil;
    }

    /**
     * Per order that counts towards the balance: what the parent paid, the
     * commission kept, and what is left for the tutor.
     *
     * @return Collection<int, array{pesanan: Pesanan, total: string, komisi: string, bersih: string}>
     */
    public function rincian(string $idTutor): Collection
    {
        return Pesanan::query()
            ->where('id_tutor', $idTutor)
            ->whereIn('status', [StatusPesanan::Dibayar, StatusPesanan::Selesai])
            ->with(['siswa', 'mapel', 'komisi'])
            ->withSum(['pembayaran as dibayar' => fn ($query) => $query->where('status', StatusPembayaran::Berhasil)], 'jumlah')
            ->latest()
            ->get()
            ->map(function (Pesanan $pesanan): array {
                $total = self::uang($pesanan->dibayar ?? 0);
                $komisi = self::uang($pesanan->komisi->nominal ?? 0);

                return ['pesanan' => $pesanan, 'total' => $total, 'komisi' => $komisi, 'bersih' => bcsub($total, $komisi, 2)];
            });
    }

    /**
     * @return array{pendapatan: string, komisi: string, dicairkan: string, saldo: string}
     */
    public function satu(string $idTutor): array
    {
        return $this->untuk([$idTutor])[$idTutor];
    }

    private static function uang(int|float|string $nilai): string
    {
        return bcadd((string) $nilai, '0', 2);
    }
}
