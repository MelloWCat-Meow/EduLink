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
}
