<?php

namespace App\Models;

use App\Enums\Kehadiran;
use App\Models\Concerns\HasPrefixedKey;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id_sesi
 * @property string $id_pesanan
 * @property Carbon $waktu
 * @property Kehadiran|null $kehadiran
 * @property string|null $catatan
 * @property string|null $tautan_meeting
 * @property Carbon|null $pengingat_dikirim_pada
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
            'pengingat_dikirim_pada' => 'datetime',
        ];
    }

    /**
     * Sessions of one tutor's paid orders, the ones that can still be logged.
     */
    #[Scope]
    protected function milikTutor(Builder $query, string $idTutor): void
    {
        $query->whereHas('pesanan', fn (Builder $pesanan) => $pesanan->where('id_tutor', $idTutor));
    }

    /**
     * @return HasMany<PermintaanJadwalUlang, $this>
     */
    public function permintaanJadwalUlang(): HasMany
    {
        return $this->hasMany(PermintaanJadwalUlang::class, 'id_sesi', 'id_sesi');
    }

    /**
     * @return BelongsTo<Pesanan, $this>
     */
    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan', 'id_pesanan');
    }
}
