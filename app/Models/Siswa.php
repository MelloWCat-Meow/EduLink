<?php

namespace App\Models;

use App\Models\Concerns\HasPrefixedKey;
use Database\Factories\SiswaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A student (Siswa) registered under a guardian account.
 *
 * @property string $id_siswa
 * @property string $id_akun
 * @property string $nama
 * @property string $jenjang
 * @property string $kontak_wali
 */
#[Fillable(['nama', 'jenjang', 'kontak_wali'])]
class Siswa extends Model
{
    /** @use HasFactory<SiswaFactory> */
    use HasFactory, HasPrefixedKey;

    protected $table = 'siswa';

    protected $primaryKey = 'id_siswa';

    public $incrementing = false;

    protected $keyType = 'string';

    public function keyPrefix(): string
    {
        return 'S';
    }

    /**
     * The guardian account this student belongs to.
     *
     * @return BelongsTo<AkunPengguna, $this>
     */
    public function wali(): BelongsTo
    {
        return $this->belongsTo(AkunPengguna::class, 'id_akun', 'id_akun');
    }

    /**
     * @return HasMany<Pesanan, $this>
     */
    public function pesanan(): HasMany
    {
        return $this->hasMany(Pesanan::class, 'id_siswa', 'id_siswa');
    }
}
