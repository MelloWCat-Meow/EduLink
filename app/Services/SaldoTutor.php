<?php

namespace App\Services;

use App\Enums\StatusPembayaran;
use App\Enums\StatusPencairan;
use App\Enums\StatusPesanan;
use App\Models\KomisiBiayaLayanan;
use App\Models\Pembayaran;
use App\Models\PencairanDanaTutor;
use App\Models\PengembalianDana;
use App\Models\Pesanan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * A tutor's payout balance:
 *   (successful payments - refunds) - platform commission - earlier payouts.
 *
 * Orders that count: paid, finished, and paid orders the admin cancelled
 * (those have a pengembalian_dana row). For a cancelled order only the
 * sessions held count: the refund covers the rest, and its commission row
 * was already lowered to the same share when it was cancelled. Earlier
 * payouts are the approved and paid ones; requests still waiting are not
 * deducted yet.
 */
class SaldoTutor
{
    /**
     * @param  list<string>  $idTutor
     * @return array<string, array{pendapatan: string, komisi: string, dicairkan: string, saldo: string}>
     */
    public function untuk(array $idTutor): array
    {
        $pesananDihitung = fn () => $this->pesananDihitung(Pesanan::query()->select('id_pesanan')->whereIn('id_tutor', $idTutor));

        $pembayaran = Pembayaran::query()
            ->join('pesanan', 'pesanan.id_pesanan', '=', 'pembayaran.id_pesanan')
            ->where('pembayaran.status', StatusPembayaran::Berhasil)
            ->whereIn('pembayaran.id_pesanan', $pesananDihitung())
            ->groupBy('pesanan.id_tutor')
            ->selectRaw('pesanan.id_tutor, SUM(pembayaran.jumlah) as total')
            ->pluck('total', 'id_tutor');

        $pengembalian = PengembalianDana::query()
            ->join('pesanan', 'pesanan.id_pesanan', '=', 'pengembalian_dana.id_pesanan')
            ->whereIn('pengembalian_dana.id_pesanan', $pesananDihitung())
            ->groupBy('pesanan.id_tutor')
            ->selectRaw('pesanan.id_tutor, SUM(pengembalian_dana.nominal) as total')
            ->pluck('total', 'id_tutor');

        $komisi = KomisiBiayaLayanan::query()
            ->join('pesanan', 'pesanan.id_pesanan', '=', 'komisi_biaya_layanan.id_pesanan')
            ->whereIn('komisi_biaya_layanan.id_pesanan', $pesananDihitung())
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
            $masuk = bcsub(self::uang($pembayaran[$id] ?? 0), self::uang($pengembalian[$id] ?? 0), 2);
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
     * Per order that counts towards the balance: what the parent paid, what
     * went back as a refund, the commission kept, and what is left for the tutor.
     *
     * @return Collection<int, array{pesanan: Pesanan, total: string, pengembalian: string, komisi: string, bersih: string}>
     */
    public function rincian(string $idTutor): Collection
    {
        return $this->pesananDihitung(Pesanan::query()->where('id_tutor', $idTutor))
            ->with(['siswa', 'mapel', 'komisi', 'pengembalianDana'])
            ->withSum(['pembayaran as dibayar' => fn ($query) => $query->where('status', StatusPembayaran::Berhasil)], 'jumlah')
            ->latest()
            ->get()
            ->map(function (Pesanan $pesanan): array {
                $total = self::uang($pesanan->dibayar ?? 0);
                $pengembalian = self::uang($pesanan->pengembalianDana->nominal ?? 0);
                $komisi = self::uang($pesanan->komisi->nominal ?? 0);

                return [
                    'pesanan' => $pesanan,
                    'total' => $total,
                    'pengembalian' => $pengembalian,
                    'komisi' => $komisi,
                    'bersih' => bcsub(bcsub($total, $pengembalian, 2), $komisi, 2),
                ];
            });
    }

    /**
     * @return array{pendapatan: string, komisi: string, dicairkan: string, saldo: string}
     */
    public function satu(string $idTutor): array
    {
        return $this->untuk([$idTutor])[$idTutor];
    }

    /**
     * @template TBuilder of Builder<Pesanan>
     *
     * @param  TBuilder  $query
     * @return TBuilder
     */
    private function pesananDihitung(Builder $query): Builder
    {
        return $query->where(fn (Builder $pesanan) => $pesanan
            ->whereIn('status', [StatusPesanan::Dibayar, StatusPesanan::Selesai])
            ->orWhere(fn (Builder $batal) => $batal->where('status', StatusPesanan::Batal)->whereHas('pengembalianDana')));
    }

    private static function uang(int|float|string $nilai): string
    {
        return bcadd((string) $nilai, '0', 2);
    }
}
