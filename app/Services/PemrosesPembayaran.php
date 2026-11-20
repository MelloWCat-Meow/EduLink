<?php

namespace App\Services;

use App\Enums\MetodePembayaran;
use App\Enums\StatusPembayaran;
use App\Enums\StatusPesanan;
use App\Exceptions\TransisiStatusTidakSah;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use Illuminate\Support\Facades\DB;

/**
 * Payment attempts and the payment gateway's notifications.
 *
 * The gateway may send the same notification more than once, so handling is
 * idempotent: a payment that already has a final status is left as it is, and
 * an order never gets a second successful payment.
 */
class PemrosesPembayaran
{
    public function __construct(private AlurPesanan $alurPesanan) {}

    /**
     * Starts (or reuses) the pending payment attempt for an order awaiting payment.
     */
    public function mulai(Pesanan $pesanan, MetodePembayaran $metode): Pembayaran
    {
        if ($this->alurPesanan->kedaluwarsakan($pesanan)) {
            throw new TransisiStatusTidakSah('Batas waktu pembayaran sudah lewat, jadi pesanan dibatalkan otomatis.');
        }

        return DB::transaction(function () use ($pesanan, $metode): Pembayaran {
            $terkunci = Pesanan::query()->whereKey($pesanan->getKey())->lockForUpdate()->firstOrFail();

            if ($terkunci->status !== StatusPesanan::MenungguPembayaran) {
                throw new TransisiStatusTidakSah("Pesanan berstatus \"{$terkunci->status->label()}\" tidak sedang menunggu pembayaran.");
            }

            $menunggu = $terkunci->pembayaran()->where('status', StatusPembayaran::Pending)->first();

            if ($menunggu !== null) {
                $menunggu->update(['metode' => $metode]);

                return $menunggu;
            }

            return $terkunci->pembayaran()->create([
                'metode' => $metode,
                'jumlah' => $terkunci->total_biaya,
                'status' => StatusPembayaran::Pending,
            ]);
        });
    }

    /**
     * Handles a gateway notification. Returns the payment as it stands afterwards.
     */
    public function terimaNotifikasi(string $idPembayaran, string $idPesanan, StatusPembayaran $status): Pembayaran
    {
        return DB::transaction(function () use ($idPembayaran, $idPesanan, $status): Pembayaran {
            $pembayaran = Pembayaran::query()
                ->whereKey($idPembayaran)
                ->where('id_pesanan', $idPesanan)
                ->lockForUpdate()
                ->firstOrFail();

            if ($pembayaran->status !== StatusPembayaran::Pending || $status === StatusPembayaran::Pending) {
                return $pembayaran;
            }

            $pesanan = Pesanan::query()->whereKey($idPesanan)->lockForUpdate()->firstOrFail();

            if ($status === StatusPembayaran::Berhasil && $this->bisaDibayar($pesanan)) {
                $pembayaran->update(['status' => StatusPembayaran::Berhasil, 'diproses_pada' => now()]);
                $this->alurPesanan->tandaiDibayar($pesanan);

                return $pembayaran;
            }

            // A failed payment, or money for an order that can no longer be paid
            // (expired, cancelled or already paid): the attempt is closed as failed.
            $pembayaran->update(['status' => StatusPembayaran::Gagal, 'diproses_pada' => now()]);

            return $pembayaran;
        });
    }

    private function bisaDibayar(Pesanan $pesanan): bool
    {
        if ($pesanan->isHoldExpired()) {
            $this->alurPesanan->kedaluwarsakan($pesanan);

            return false;
        }

        return $pesanan->status === StatusPesanan::MenungguPembayaran
            && $pesanan->pembayaran()->where('status', StatusPembayaran::Berhasil)->doesntExist();
    }
}
