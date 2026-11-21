<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One switch of an account off or on by an admin. Written only by App\Services\KelolaAkun.
 *
 * @property bool $aktif
 * @property string $alasan
 * @property string|null $oleh
 * @property Carbon $dicatat_pada
 */
#[Fillable(['aktif', 'alasan', 'oleh', 'dicatat_pada'])]
class RiwayatStatusAkun extends Model
{
    protected $table = 'riwayat_status_akun';

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['aktif' => 'boolean', 'dicatat_pada' => 'datetime'];
    }

    /**
     * @return BelongsTo<AkunPengguna, $this>
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(AkunPengguna::class, 'oleh', 'id_akun');
    }
}
