<?php

namespace App\Models;

use Database\Factories\MataPelajaranFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string $kode_mapel
 * @property string $nama
 * @property string $jenjang
 */
#[Fillable(['kode_mapel', 'nama', 'jenjang'])]
class MataPelajaran extends Model
{
    /** @use HasFactory<MataPelajaranFactory> */
    use HasFactory;

    protected $table = 'mata_pelajaran';

    protected $primaryKey = 'kode_mapel';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    public function label(): string
    {
        return $this->nama.' '.$this->jenjang;
    }

    /**
     * @return BelongsToMany<Tutor, $this>
     */
    public function tutor(): BelongsToMany
    {
        return $this->belongsToMany(Tutor::class, 'tutor_mata_pelajaran', 'kode_mapel', 'id_tutor', 'kode_mapel', 'id_tutor');
    }
}
