<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Key/value platform settings.
 *
 * @property string $kunci
 * @property string $nilai
 */
#[Fillable(['kunci', 'nilai'])]
class Pengaturan extends Model
{
    public const KOMISI_PERSEN = 'komisi_persen';

    public const DEFAULT_KOMISI_PERSEN = '15';

    protected $table = 'pengaturan';

    protected $primaryKey = 'kunci';

    public $incrementing = false;

    protected $keyType = 'string';

    public static function nilai(string $kunci, ?string $default = null): ?string
    {
        return static::query()->whereKey($kunci)->value('nilai') ?? $default;
    }

    public static function simpan(string $kunci, string $nilai): void
    {
        static::query()->updateOrCreate(['kunci' => $kunci], ['nilai' => $nilai]);
    }

    /**
     * The commission percentage applied to orders paid from now on.
     */
    public static function komisiPersen(): string
    {
        return static::nilai(self::KOMISI_PERSEN, self::DEFAULT_KOMISI_PERSEN);
    }
}
