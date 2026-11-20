<?php

namespace App\Enums;

enum ModeBelajar: string
{
    case Online = 'online';
    case KunjunganRumah = 'kunjungan_rumah';

    public function label(): string
    {
        return match ($this) {
            self::Online => 'Online',
            self::KunjunganRumah => 'Tutor datang ke rumah',
        };
    }
}
