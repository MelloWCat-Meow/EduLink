<?php

namespace App\Models;

use App\Enums\StatusJadwalUlang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A request to move one session. Written only by App\Services\JadwalUlang.
 *
 * @property int $id
 * @property string $id_sesi
 * @property string $id_akun
 * @property Carbon $waktu_lama
 * @property Carbon $waktu_baru
 * @property string $alasan
 * @property StatusJadwalUlang $status
 * @property Carbon|null $dijawab_pada
 * @property string|null $alasan_penolakan
 */
class PermintaanJadwalUlang extends Model
{
    protected $table = 'permintaan_jadwal_ulang';

    /**
     * @var list<string>
     */
    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'waktu_lama' => 'datetime',
            'waktu_baru' => 'datetime',
            'dijawab_pada' => 'datetime',
            'status' => StatusJadwalUlang::class,
        ];
    }

    /**
     * @return BelongsTo<SesiPembelajaran, $this>
     */
    public function sesi(): BelongsTo
    {
        return $this->belongsTo(SesiPembelajaran::class, 'id_sesi', 'id_sesi');
    }

    /**
     * @return BelongsTo<AkunPengguna, $this>
     */
    public function pengaju(): BelongsTo
    {
        return $this->belongsTo(AkunPengguna::class, 'id_akun', 'id_akun');
    }
}
