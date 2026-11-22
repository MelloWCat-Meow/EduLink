<?php

namespace App\Enums;

enum Kehadiran: string
{
    case Hadir = 'hadir';
    case TidakHadir = 'tidak_hadir';

    public function label(): string
    {
        return match ($this) {
            // A session the tutor turned up for; an absent student without notice still counts as held.
            self::Hadir => 'Terlaksana',
            self::TidakHadir => 'Siswa tidak hadir',
        };
    }
}
