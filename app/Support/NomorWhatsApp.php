<?php

namespace App\Support;

/**
 * Indonesian phone numbers for wa.me links: international format, digits only.
 * "0812-3456 7890", "+62 812 3456 7890" and "6281234567890" all become
 * "6281234567890".
 */
class NomorWhatsApp
{
    public static function normalisasi(?string $nomor): ?string
    {
        $angka = preg_replace('/\D/', '', (string) $nomor);

        if ($angka === '') {
            return null;
        }

        if (str_starts_with($angka, '0')) {
            $angka = '62'.substr($angka, 1);
        } elseif (str_starts_with($angka, '8')) {
            $angka = '62'.$angka;
        }

        // Indonesian mobile numbers: 62 8xx followed by 7 to 12 more digits.
        return preg_match('/^628\d{7,12}$/', $angka) === 1 ? $angka : null;
    }

    /**
     * Click-to-chat link with a prefilled message, or null when the number is unusable.
     */
    public static function tautan(?string $nomor, string $pesan): ?string
    {
        $normal = self::normalisasi($nomor);

        return $normal === null ? null : 'https://wa.me/'.$normal.'?text='.rawurlencode($pesan);
    }
}
