<?php

namespace App\Models;

use App\Enums\JenisDokumen;
use App\Enums\StatusVerifikasi;
use App\Models\Concerns\HasPrefixedKey;
use Database\Factories\DokumenVerifikasiFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id_dokumen
 * @property string $id_tutor
 * @property JenisDokumen $jenis
 * @property string $berkas
 * @property StatusVerifikasi $status_verifikasi
 * @property string|null $alasan_penolakan
 */
#[Fillable(['jenis', 'berkas', 'status_verifikasi', 'alasan_penolakan', 'diperiksa_pada'])]
class DokumenVerifikasi extends Model
{
    /** @use HasFactory<DokumenVerifikasiFactory> */
    use HasFactory, HasPrefixedKey;

    protected $table = 'dokumen_verifikasi';

    protected $primaryKey = 'id_dokumen';

    public $incrementing = false;

    protected $keyType = 'string';

    public function keyPrefix(): string
    {
        return 'DV';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jenis' => JenisDokumen::class,
            'status_verifikasi' => StatusVerifikasi::class,
            'diperiksa_pada' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Tutor, $this>
     */
    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class, 'id_tutor', 'id_tutor');
    }
}
