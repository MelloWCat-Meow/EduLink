<?php

namespace App\Enums;

enum JenisKeluhan: string
{
    case TutorTidakHadir = 'tutor_tidak_hadir';
    case KualitasPengajaran = 'kualitas_pengajaran';
    case Jadwal = 'jadwal';
    case Pembayaran = 'pembayaran';
    case Lainnya = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::TutorTidakHadir => 'Tutor tidak datang atau terlambat',
            self::KualitasPengajaran => 'Cara mengajar',
            self::Jadwal => 'Jadwal sesi',
            self::Pembayaran => 'Pembayaran',
            self::Lainnya => 'Lainnya',
        };
    }
}
