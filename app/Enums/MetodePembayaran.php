<?php

namespace App\Enums;

enum MetodePembayaran: string
{
    case VirtualAccount = 'virtual_account';
    case Qris = 'qris';
    case EWallet = 'e_wallet';

    public function label(): string
    {
        return match ($this) {
            self::VirtualAccount => 'Transfer virtual account',
            self::Qris => 'QRIS',
            self::EWallet => 'Dompet digital',
        };
    }
}
