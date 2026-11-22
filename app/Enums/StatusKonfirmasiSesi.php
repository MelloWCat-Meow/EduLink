<?php

namespace App\Enums;

/**
 * A logged session on its way to being paid out to the tutor.
 */
enum StatusKonfirmasiSesi: string
{
    case MenungguKonfirmasi = 'menunggu_konfirmasi';
    case Dikonfirmasi = 'dikonfirmasi';
    case DikonfirmasiOtomatis = 'dikonfirmasi_otomatis';
    case Disanggah = 'disanggah';
    case TidakSah = 'tidak_sah';

    public function label(): string
    {
        return match ($this) {
            self::MenungguKonfirmasi => 'Menunggu konfirmasi orang tua',
            self::Dikonfirmasi => 'Dikonfirmasi',
            self::DikonfirmasiOtomatis => 'Dikonfirmasi otomatis',
            self::Disanggah => 'Disanggah, menunggu admin',
            self::TidakSah => 'Tidak sah, dana dikembalikan',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::MenungguKonfirmasi => 'tunggu',
            self::Dikonfirmasi, self::DikonfirmasiOtomatis => 'baik',
            self::Disanggah => 'info',
            self::TidakSah => 'buruk',
        };
    }

    /**
     * The tutor earns the session: its share is available and its commission recognised.
     */
    public function menjadiHakTutor(): bool
    {
        return in_array($this, [self::Dikonfirmasi, self::DikonfirmasiOtomatis], true);
    }

    /**
     * Settled for good; an order is finished when every session is.
     */
    public function tuntas(): bool
    {
        return $this->menjadiHakTutor() || $this === self::TidakSah;
    }

    /**
     * @return list<self>
     */
    public static function hakTutor(): array
    {
        return [self::Dikonfirmasi, self::DikonfirmasiOtomatis];
    }
}
