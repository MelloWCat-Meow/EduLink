<?php

namespace App\Enums;

enum StatusPembayaran: string
{
    case Pending = 'pending';
    case Berhasil = 'berhasil';
    case Gagal = 'gagal';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Berhasil => 'Berhasil',
            self::Gagal => 'Gagal',
        };
    }
}
