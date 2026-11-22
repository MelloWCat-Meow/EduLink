<?php

namespace App\Enums;

enum StatusJadwalUlang: string
{
    case Menunggu = 'menunggu';
    case Disetujui = 'disetujui';
    case Ditolak = 'ditolak';
    case Kedaluwarsa = 'kedaluwarsa';

    public function label(): string
    {
        return match ($this) {
            self::Menunggu => 'Menunggu jawaban',
            self::Disetujui => 'Disetujui',
            self::Ditolak => 'Ditolak',
            self::Kedaluwarsa => 'Kedaluwarsa',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Menunggu => 'tunggu',
            self::Disetujui => 'baik',
            self::Ditolak => 'buruk',
            self::Kedaluwarsa => 'netral',
        };
    }
}
