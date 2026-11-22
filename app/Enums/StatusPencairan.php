<?php

namespace App\Enums;

enum StatusPencairan: string
{
    case Menunggu = 'menunggu';
    case Disetujui = 'disetujui';
    case Dibayar = 'dibayar';

    public function label(): string
    {
        return match ($this) {
            self::Menunggu => 'Menunggu persetujuan',
            self::Disetujui => 'Disetujui',
            self::Dibayar => 'Sudah dibayar',
        };
    }

    /**
     * Label colour family for <x-dasbor.label>.
     */
    public function tone(): string
    {
        return match ($this) {
            self::Menunggu => 'tunggu',
            self::Disetujui => 'info',
            self::Dibayar => 'baik',
        };
    }
}
