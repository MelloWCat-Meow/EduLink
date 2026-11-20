<?php

namespace App\Models;

use App\Models\Concerns\HasPrefixedKey;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The platform's cut of one order, fixed at the moment the order was paid.
 *
 * @property string $id_komisi
 * @property string $id_pesanan
 * @property string $persentase
 * @property string $nominal
 */
#[Fillable(['persentase', 'nominal'])]
class KomisiBiayaLayanan extends Model
{
    use HasPrefixedKey;

    protected $table = 'komisi_biaya_layanan';

    protected $primaryKey = 'id_komisi';

    public $incrementing = false;

    protected $keyType = 'string';

    public function keyPrefix(): string
    {
        return 'KM';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'persentase' => 'decimal:2',
            'nominal' => 'decimal:2',
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
