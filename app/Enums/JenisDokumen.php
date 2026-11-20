<?php

namespace App\Enums;

enum JenisDokumen: string
{
    case Ijazah = 'ijazah';
    case Ktp = 'ktp';
    case Sertifikat = 'sertifikat';

    public function label(): string
    {
        return match ($this) {
            self::Ijazah => 'Ijazah',
            self::Ktp => 'KTP',
            self::Sertifikat => 'Sertifikat',
        };
    }
}
