<?php

namespace App\Models;

use App\Enums\StatusVerifikasiTutor;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One step of a tutor's verification: sent by the tutor, or decided by an
 * admin. Written only by App\Services\PendaftaranTutor and VerifikasiTutor.
 *
 * @property StatusVerifikasiTutor $status
 * @property string|null $catatan
 * @property array<string, string|null>|null $bagian
 * @property string|null $id_akun
 * @property Carbon $dicatat_pada
 */
#[Fillable(['status', 'catatan', 'bagian', 'id_akun', 'dicatat_pada'])]
class RiwayatVerifikasiTutor extends Model
{
    protected $table = 'riwayat_verifikasi_tutor';

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => StatusVerifikasiTutor::class,
            'bagian' => 'array',
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
