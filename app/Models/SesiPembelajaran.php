<?php

namespace App\Models;

use App\Enums\Kehadiran;
use App\Models\Concerns\HasPrefixedKey;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id_sesi
 * @property string $id_pesanan
 * @property Carbon $waktu
 * @property Kehadiran|null $kehadiran
 * @property string|null $catatan
 * @property string|null $tautan_meeting
 */
#[Fillable(['waktu', 'kehadiran', 'catatan', 'tautan_meeting'])]
class SesiPembelajaran extends Model
{
    use HasPrefixedKey;

    protected $table = 'sesi_pembelajaran';

    protected $primaryKey = 'id_sesi';

    public $incrementing = false;

    protected $keyType = 'string';

    public function keyPrefix(): string
    {
        return 'SES';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'waktu' => 'datetime',
            'kehadiran' => Kehadiran::class,
        ];
    }

    /**
     * @return BelongsTo<Pesanan, $this>
     */
    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan', 'id_pesanan');
    }
}
