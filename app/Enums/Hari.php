<?php

namespace App\Enums;

/**
 * Day of a weekly availability slot. Values follow ISO-8601 (Senin = 1).
 */
enum Hari: string
{
    case Senin = 'senin';
    case Selasa = 'selasa';
    case Rabu = 'rabu';
    case Kamis = 'kamis';
    case Jumat = 'jumat';
    case Sabtu = 'sabtu';
    case Minggu = 'minggu';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function isoWeekday(): int
    {
        return array_search($this, self::cases(), true) + 1;
    }

    public static function fromIsoWeekday(int $weekday): self
    {
        return self::cases()[$weekday - 1];
    }
}
