<?php

namespace App\Models;

use App\Enums\StatusPesanan;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One entry of an order's timeline. Written only by App\Services\AlurPesanan.
 *
 * @property StatusPesanan $status
 * @property string|null $catatan
 * @property string|null $id_akun
 * @property Carbon $dicatat_pada
 */
#[Fillable(['status', 'catatan', 'id_akun', 'dicatat_pada'])]
class RiwayatStatusPesanan extends Model
{
    protected $table = 'riwayat_status_pesanan';

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => StatusPesanan::class,
            'dicatat_pada' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<AkunPengguna, $this>
     */
    public function akun(): BelongsTo
    {
        return $this->belongsTo(AkunPengguna::class, 'id_akun', 'id_akun');
    }
}
