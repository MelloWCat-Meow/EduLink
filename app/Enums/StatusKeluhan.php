<?php

namespace App\Enums;

enum StatusKeluhan: string
{
    case Baru = 'baru';
    case Diproses = 'diproses';
    case Selesai = 'selesai';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /**
     * A complaint only moves forward: baru, then diproses, then selesai.
     */
    public function canMoveTo(self $next): bool
    {
        return match ($this) {
            self::Baru => in_array($next, [self::Diproses, self::Selesai], true),
            self::Diproses => $next === self::Selesai,
            self::Selesai => false,
        };
    }

    /**
     * Label colour family for <x-dasbor.label>.
     */
    public function tone(): string
    {
        return match ($this) {
            self::Baru => 'tunggu',
            self::Diproses => 'info',
            self::Selesai => 'baik',
        };
    }
}
