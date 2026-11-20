<?php

namespace App\Enums;

enum StatusJadwal: string
{
    case Tersedia = 'tersedia';
    case Ditahan = 'ditahan';
    case Terisi = 'terisi';
    // Removed by the tutor but kept because past orders point at it. Never bookable.
    case Nonaktif = 'nonaktif';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /**
     * Label colour family for <x-dasbor.label>.
     */
    public function tone(): string
    {
        return match ($this) {
            self::Tersedia => 'baik',
            self::Ditahan => 'tunggu',
            self::Terisi => 'netral',
            self::Nonaktif => 'netral',
        };
    }
}
