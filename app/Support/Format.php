<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Indonesian display formats: Rp1.250.000, 20 November 2026, 16.00 WIB.
 */
class Format
{
    public static function rupiah(int|float|string|null $nilai): string
    {
        $angka = (float) ($nilai ?? 0);
        $tanda = $angka < 0 ? '−' : '';

        return $tanda.'Rp'.number_format(abs($angka), 0, ',', '.');
    }

    public static function tanggal(?CarbonInterface $waktu, bool $denganHari = false): string
    {
        if ($waktu === null) {
            return '–';
        }

        return $waktu->locale('id')->translatedFormat($denganHari ? 'l, j F Y' : 'j F Y');
    }

    public static function tanggalPendek(?CarbonInterface $waktu): string
    {
        return $waktu === null ? '–' : $waktu->locale('id')->translatedFormat('j M Y');
    }

    public static function jam(?CarbonInterface $waktu): string
    {
        return $waktu === null ? '–' : $waktu->format('H.i').' WIB';
    }

    public static function waktu(?CarbonInterface $waktu, bool $denganHari = false): string
    {
        return $waktu === null ? '–' : self::tanggal($waktu, $denganHari).', '.self::jam($waktu);
    }

    /**
     * "18 jam 20 menit lagi", for hold deadlines.
     */
    public static function sisaWaktu(?CarbonInterface $batas): string
    {
        if ($batas === null || $batas->isPast()) {
            return 'sudah lewat';
        }

        $menit = (int) now()->diffInMinutes($batas);
        $jam = intdiv($menit, 60);
        $sisaMenit = $menit % 60;

        return trim(($jam > 0 ? $jam.' jam ' : '').($sisaMenit > 0 || $jam === 0 ? $sisaMenit.' menit' : '')).' lagi';
    }

    /**
     * "November 2026" for a YYYY-MM period.
     */
    public static function periode(string $periode): string
    {
        return Carbon::createFromFormat('!Y-m', $periode)->locale('id')->translatedFormat('F Y');
    }

    /**
     * "312 KB" or "1,4 MB", for uploaded files.
     */
    public static function ukuranBerkas(?int $bytes): string
    {
        if ($bytes === null) {
            return 'ukuran tidak diketahui';
        }

        if ($bytes < 1024 * 1024) {
            return max(1, (int) round($bytes / 1024)).' KB';
        }

        return number_format($bytes / 1024 / 1024, 1, ',', '.').' MB';
    }
}
