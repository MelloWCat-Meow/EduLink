<?php

namespace App\Enums;

enum JenisKeluhan: string
{
    case TutorTidakHadir = 'tutor_tidak_hadir';
    case KualitasPengajaran = 'kualitas_pengajaran';
    case Jadwal = 'jadwal';
    case Pembayaran = 'pembayaran';
    case Lainnya = 'lainnya';
    // Opened by the system when a parent disputes a logged session.
    case SesiDisanggah = 'sesi_disanggah';

    public function label(): string
    {
        return match ($this) {
            self::TutorTidakHadir => 'Tutor tidak datang atau terlambat',
            self::KualitasPengajaran => 'Cara mengajar',
            self::Jadwal => 'Jadwal sesi',
            self::Pembayaran => 'Pembayaran',
            self::Lainnya => 'Lainnya',
            self::SesiDisanggah => 'Sanggahan sesi',
        };
    }

    /**
     * The kinds a person can pick in the complaint form; disputes come from the session itself.
     *
     * @return list<self>
     */
    public static function pilihanFormulir(): array
    {
        return array_values(array_filter(self::cases(), fn (self $jenis): bool => $jenis !== self::SesiDisanggah));
    }
}
