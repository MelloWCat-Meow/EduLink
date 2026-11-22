<?php

namespace App\Models;

use App\Enums\MetodePembayaran;
use App\Enums\StatusPembayaran;
use App\Models\Concerns\HasPrefixedKey;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One payment attempt for an order.
 *
 * @property string $id_pembayaran
 * @property string $id_pesanan
 * @property MetodePembayaran $metode
 * @property string $jumlah
 * @property StatusPembayaran $status
 * @property Carbon|null $diproses_pada
 * @property Carbon $created_at
 */
#[Fillable(['metode', 'jumlah', 'status', 'diproses_pada'])]
class Pembayaran extends Model
{
    use HasPrefixedKey;

    protected $table = 'pembayaran';

    protected $primaryKey = 'id_pembayaran';

    public $incrementing = false;

    protected $keyType = 'string';

    public function keyPrefix(): string
    {
        return 'PAY';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metode' => MetodePembayaran::class,
            'status' => StatusPembayaran::class,
            'jumlah' => 'decimal:2',
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
}
