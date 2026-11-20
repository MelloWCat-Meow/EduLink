<?php

namespace App\Enums;

enum StatusJadwal: string
{
    case Tersedia = 'tersedia';
    case Ditahan = 'ditahan';
    case Terisi = 'terisi';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
