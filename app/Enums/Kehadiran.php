<?php

namespace App\Enums;

enum Kehadiran: string
{
    case Hadir = 'hadir';
    case TidakHadir = 'tidak_hadir';

    public function label(): string
    {
        return match ($this) {
            self::Hadir => 'Hadir',
            self::TidakHadir => 'Tidak hadir',
        };
    }
}
