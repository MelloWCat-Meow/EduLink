<?php

namespace App\Enums;

/**
 * The numbered steps of the tutor application form. The values are the URL
 * segment under /daftar-tutor and the keys the admin uses to flag a section.
 */
enum LangkahPendaftaran: string
{
    case DataDiri = 'data-diri';
    case Profil = 'profil';
    case Dokumen = 'dokumen';
    case Jadwal = 'jadwal';
    case Kirim = 'kirim';

    public function nomor(): int
    {
        return array_search($this, self::cases(), true) + 1;
    }

    public function label(): string
    {
        return match ($this) {
            self::DataDiri => 'Data diri',
            self::Profil => 'Profil mengajar',
            self::Dokumen => 'Dokumen',
            self::Jadwal => 'Jadwal',
            self::Kirim => 'Periksa dan kirim',
        };
    }

    /**
     * Steps that hold data, which the admin can flag for correction.
     *
     * @return list<self>
     */
    public static function bagian(): array
    {
        return [self::DataDiri, self::Profil, self::Dokumen, self::Jadwal];
    }
}
