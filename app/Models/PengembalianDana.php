<?php

namespace App\Models;

use App\Enums\StatusPengembalian;
use App\Models\Concerns\HasPrefixedKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Refund for the sessions of a cancelled paid order that did not take place.
 * Written only by App\Services\PengembalianSesi and App\Services\ProsesPengembalian.
 *
 * @property string $id_pengembalian
 * @property string $id_pesanan
 * @property string $nominal
 * @property StatusPengembalian $status
 * @property string|null $catatan
 * @property string|null $diproses_oleh
 * @property Carbon|null $diproses_pada
 */
class PengembalianDana extends Model
{
    use HasPrefixedKey;

    protected $table = 'pengembalian_dana';

    protected $primaryKey = 'id_pengembalian';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $guarded = [];

    public function keyPrefix(): string
    {
        return 'RF';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nominal' => 'decimal:2',
            'status' => StatusPengembalian::class,
            'diproses_pada' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Pesanan, $this>
     */
    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan', 'id_pesanan');
    }

    /**
     * The sessions this refund pays back.
     *
     * @return HasMany<SesiPembelajaran, $this>
     */
    public function sesi(): HasMany
    {
        return $this->hasMany(SesiPembelajaran::class, 'id_pengembalian', 'id_pengembalian');
    }

    /**
     * @return BelongsTo<AkunPengguna, $this>
     */
    public function pemroses(): BelongsTo
    {
        return $this->belongsTo(AkunPengguna::class, 'diproses_oleh', 'id_akun');
    }
}
