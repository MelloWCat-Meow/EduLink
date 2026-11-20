<?php

namespace App\Enums;

/**
 * Where a tutor's application stands. Only terverifikasi tutors are listed in
 * search; draf and perlu_perbaikan may still be edited by the tutor.
 */
enum StatusVerifikasiTutor: string
{
    case Draf = 'draf';
    case Menunggu = 'menunggu';
    case PerluPerbaikan = 'perlu_perbaikan';
    case Ditolak = 'ditolak';
    case Terverifikasi = 'terverifikasi';

    public function label(): string
    {
        return match ($this) {
            self::Draf => 'Draf',
            self::Menunggu => 'Menunggu pemeriksaan',
            self::PerluPerbaikan => 'Perlu perbaikan',
            self::Ditolak => 'Ditolak',
            self::Terverifikasi => 'Terverifikasi',
        };
    }

    /**
     * Label colour family for <x-dasbor.label>.
     */
    public function tone(): string
    {
        return match ($this) {
            self::Draf => 'netral',
            self::Menunggu => 'tunggu',
            self::PerluPerbaikan => 'info',
            self::Ditolak => 'buruk',
            self::Terverifikasi => 'baik',
        };
    }

    /**
     * The tutor may still change the application and send it.
     */
    public function bisaDiubah(): bool
    {
        return in_array($this, [self::Draf, self::PerluPerbaikan], true);
    }
}
