<?php

namespace App\Enums;

/**
 * Order lifecycle. Every allowed move is listed in transitions(); the order
 * workflow service is the only place that changes an order's status.
 */
enum StatusPesanan: string
{
    case MenungguKonfirmasiTutor = 'menunggu_konfirmasi_tutor';
    case Ditolak = 'ditolak';
    case MenungguPembayaran = 'menunggu_pembayaran';
    case Dibayar = 'dibayar';
    case Selesai = 'selesai';
    case Batal = 'batal';

    public function label(): string
    {
        return match ($this) {
            self::MenungguKonfirmasiTutor => 'Menunggu konfirmasi tutor',
            self::Ditolak => 'Ditolak tutor',
            self::MenungguPembayaran => 'Menunggu pembayaran',
            self::Dibayar => 'Dibayar',
            self::Selesai => 'Selesai',
            self::Batal => 'Batal',
        };
    }

    /**
     * @return list<self>
     */
    public function transitions(): array
    {
        return match ($this) {
            self::MenungguKonfirmasiTutor => [self::MenungguPembayaran, self::Ditolak, self::Batal],
            self::MenungguPembayaran => [self::Dibayar, self::Batal],
            self::Dibayar => [self::Selesai, self::Batal],
            self::Ditolak, self::Selesai, self::Batal => [],
        };
    }

    public function canMoveTo(self $next): bool
    {
        return in_array($next, $this->transitions(), true);
    }

    /**
     * Statuses during which the order holds its weekly slot as "ditahan".
     */
    public function holdsSlot(): bool
    {
        return in_array($this, [self::MenungguKonfirmasiTutor, self::MenungguPembayaran], true);
    }

    /**
     * Statuses that still need something to happen: they block deleting a child.
     */
    public function isActive(): bool
    {
        return in_array($this, [self::MenungguKonfirmasiTutor, self::MenungguPembayaran, self::Dibayar], true);
    }

    public function isFinal(): bool
    {
        return $this->transitions() === [];
    }

    /**
     * @return list<self>
     */
    public static function active(): array
    {
        return array_values(array_filter(self::cases(), fn (self $status): bool => $status->isActive()));
    }
}
