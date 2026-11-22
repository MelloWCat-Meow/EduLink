<?php

namespace App\Models;

use App\Enums\JenisKeluhan;
use App\Enums\StatusKeluhan;
use App\Models\Concerns\HasPrefixedKey;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id_keluhan
 * @property string $id_pesanan
 * @property string $id_akun
 * @property JenisKeluhan $jenis
 * @property StatusKeluhan $status
 * @property string $deskripsi
 * @property string|null $tanggapan_admin
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['id_pesanan', 'jenis', 'deskripsi'])]
class Keluhan extends Model
{
    use HasPrefixedKey;

    protected $table = 'keluhan';

    protected $primaryKey = 'id_keluhan';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $attributes = [
        'status' => 'baru',
    ];

    public function keyPrefix(): string
    {
        return 'KL';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jenis' => JenisKeluhan::class,
            'status' => StatusKeluhan::class,
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
     * The disputed session, for complaints opened by a dispute.
     *
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
