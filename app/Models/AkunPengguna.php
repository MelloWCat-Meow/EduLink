<?php

namespace App\Models;

use App\Enums\Peran;
use App\Models\Concerns\HasPrefixedKey;
use Database\Factories\AkunPenggunaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * A sign-in account (Akun_Pengguna). `kontak` holds the email used to sign in
 * and `kredensial` the hashed password. `peran` is deliberately not fillable:
 * it is set by the code path that creates the account, never from a request.
 *
 * @property string $id_akun
 * @property Peran $peran
 * @property string $kontak
 * @property string $nama
 */
#[Fillable(['nama', 'kontak', 'kredensial'])]
#[Hidden(['kredensial', 'remember_token'])]
class AkunPengguna extends Authenticatable
{
    /** @use HasFactory<AkunPenggunaFactory> */
    use HasFactory, HasPrefixedKey, Notifiable;

    protected $table = 'akun_pengguna';

    protected $primaryKey = 'id_akun';

    public $incrementing = false;

    protected $keyType = 'string';

    public function keyPrefix(): string
    {
        return 'AK';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'peran' => Peran::class,
            'kredensial' => 'hashed',
        ];
    }

    public function getAuthPasswordName(): string
    {
        return 'kredensial';
    }

    public function getAuthPassword(): string
    {
        return $this->kredensial;
    }

    public function hasPeran(Peran $peran): bool
    {
        return $this->peran === $peran;
    }

    /**
     * Students under this guardian account (Akun_Pengguna 1 to many Siswa).
     *
     * @return HasMany<Siswa, $this>
     */
    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class, 'id_akun', 'id_akun');
    }

    /**
     * Complaints filed by this account.
     *
     * @return HasMany<Keluhan, $this>
     */
    public function keluhan(): HasMany
    {
        return $this->hasMany(Keluhan::class, 'id_akun', 'id_akun');
    }

    /**
     * The tutor record linked to this account, for tutor accounts.
     *
     * @return HasOne<Tutor, $this>
     */
    public function tutor(): HasOne
    {
        return $this->hasOne(Tutor::class, 'id_akun', 'id_akun');
    }

    /**
     * Orders placed for this guardian's children.
     *
     * @return HasManyThrough<Pesanan, Siswa, $this>
     */
    public function pesanan(): HasManyThrough
    {
        return $this->hasManyThrough(Pesanan::class, Siswa::class, 'id_akun', 'id_siswa', 'id_akun', 'id_siswa');
    }
}
