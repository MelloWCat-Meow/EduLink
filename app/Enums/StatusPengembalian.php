<?php

namespace App\Enums;

enum StatusPengembalian: string
{
    case PerluDiproses = 'perlu_diproses';
    case Selesai = 'selesai';

    public function label(): string
    {
        return match ($this) {
            self::PerluDiproses => 'Perlu diproses',
            self::Selesai => 'Sudah dikembalikan',
        };
    }

    public function tone(): string
    {
        return $this === self::Selesai ? 'baik' : 'tunggu';
    }
}
