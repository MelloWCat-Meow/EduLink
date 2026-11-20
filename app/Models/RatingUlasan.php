<?php

namespace App\Models;

use App\Models\Concerns\HasPrefixedKey;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id_ulasan
 * @property string $id_pesanan
 * @property int $nilai
 * @property string|null $komentar
 * @property bool $ditampilkan
 * @property string|null $alasan_moderasi
 * @property Carbon $created_at
 */
#[Fillable(['nilai', 'komentar'])]
class RatingUlasan extends Model
{
    use HasPrefixedKey;

    protected $table = 'rating_ulasan';

    protected $primaryKey = 'id_ulasan';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $attributes = [
        'ditampilkan' => true,
    ];

    public function keyPrefix(): string
    {
        return 'UL';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nilai' => 'integer',
            'ditampilkan' => 'boolean',
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
