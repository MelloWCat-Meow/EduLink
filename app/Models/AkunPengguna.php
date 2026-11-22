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
use Illuminate\Support\Carbon;

/**
 * A sign-in account (Akun_Pengguna). `kontak` holds the email used to sign in
 * and `kredensial` the hashed password. `peran` is deliberately not fillable:
 * it is set by the code path that creates the account, never from a request.
 *
 * @property string $id_akun
 * @property Peran $peran
 * @property string $kontak
 * @property string|null $no_hp
 * @property Carbon|null $persetujuan_pada
 * @property string|null $versi_kebijakan
 * @property bool $aktif
 * @property Carbon|null $dinonaktifkan_pada
 * @property string|null $alasan_nonaktif
 * @property string $nama
 */
#[Fillable(['nama', 'kontak', 'no_hp', 'kredensial'])]
#[Hidden(['kredensial', 'remember_token'])]
class AkunPengguna extends Authenticatable
{
    /** @use HasFactory<AkunPenggunaFactory> */
    use HasFactory, HasPrefixedKey, Notifiable;

    protected $table = 'akun_pengguna';

    protected $primaryKey = 'id_akun';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * Matches the column default, so a new account is active before it is reloaded.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'aktif' => true,
    ];

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
            'persetujuan_pada' => 'datetime',
            'aktif' => 'boolean',
            'dinonaktifkan_pada' => 'datetime',
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

    /**
     * Notification emails go to the sign-in email, which lives in kontak.
     *
     * @return array<string, string>
     */
    public function routeNotificationForMail(): array
    {
        return [$this->kontak => $this->nama];
    }

    /**
     * The password broker identifies accounts by their sign-in email.
     */
    public function getEmailForPasswordReset(): string
    {
        return $this->kontak;
    }

    /**
     * Records agreement to the current Terms and Privacy Policy (not saved yet).
     */
    public function catatPersetujuan(): static
    {
        $this->persetujuan_pada = now();
        $this->versi_kebijakan = config('edulink.versi_kebijakan');

        return $this;
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
     * @return HasMany<RiwayatStatusAkun, $this>
     */
    public function riwayatStatus(): HasMany
    {
        return $this->hasMany(RiwayatStatusAkun::class, 'id_akun', 'id_akun');
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
