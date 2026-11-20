<?php

namespace App\Support;

/**
 * HMAC signature of a payment notification, the way a payment gateway signs
 * its webhooks. The simulator signs with the application key.
 */
class TandaTanganWebhook
{
    public static function buat(string $idPembayaran, string $idPesanan, string $status): string
    {
        return hash_hmac('sha256', implode('|', [$idPembayaran, $idPesanan, $status]), (string) config('app.key'));
    }

    public static function cocok(string $tandaTangan, string $idPembayaran, string $idPesanan, string $status): bool
    {
        return hash_equals(self::buat($idPembayaran, $idPesanan, $status), $tandaTangan);
    }
}
