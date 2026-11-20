<?php

namespace App\Enums;

enum StatusVerifikasi: string
{
    case Menunggu = 'menunggu';
    case Valid = 'valid';
    case Ditolak = 'ditolak';

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
            self::Menunggu => 'tunggu',
            self::Valid => 'baik',
            self::Ditolak => 'buruk',
        };
    }
}
