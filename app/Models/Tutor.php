<?php

namespace App\Models;

use App\Enums\LangkahPendaftaran;
use App\Enums\StatusTutor;
use App\Enums\StatusVerifikasiTutor;
use App\Models\Concerns\HasPrefixedKey;
use Database\Factories\TutorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property string $id_tutor
 * @property string|null $id_akun
 * @property string $nama
 * @property StatusTutor $status
 * @property string $kontak
 * @property string|null $no_hp
 * @property StatusVerifikasiTutor $status_verifikasi
 * @property string|null $catatan_verifikasi
 * @property array<string, string|null>|null $bagian_perbaikan
 * @property Carbon|null $diajukan_pada
 * @property float|null $rata_rating
 * @property int|null $jumlah_ulasan
 */
#[Fillable(['id_akun', 'nama', 'status', 'kontak', 'no_hp'])]
class Tutor extends Model
{
    /** @use HasFactory<TutorFactory> */
    use HasFactory, HasPrefixedKey;

    protected $table = 'tutor';

    protected $primaryKey = 'id_tutor';

    public $incrementing = false;

    protected $keyType = 'string';

    public function keyPrefix(): string
    {
        return 'T';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => StatusTutor::class,
            'status_verifikasi' => StatusVerifikasiTutor::class,
            'bagian_perbaikan' => 'array',
            'diajukan_pada' => 'datetime',
        ];
    }

    /**
     * Tutors a parent may find and book: the application was approved
     * (internal and external alike) and the profile has a rate and a district,
     * since a parent cannot book without either.
     */
    #[Scope]
    protected function tampil(Builder $query): void
    {
        $query->where('status_verifikasi', StatusVerifikasiTutor::Terverifikasi)
            ->whereHas('profil', fn (Builder $profil) => $profil->whereNotNull('tarif')->whereNotNull('lokasi')->where('lokasi', '!=', ''));
    }

    /**
     * Applications sent and waiting for the admin.
     */
    #[Scope]
    protected function menungguVerifikasi(Builder $query): void
    {
        $query->where('status_verifikasi', StatusVerifikasiTutor::Menunggu);
    }

    /**
     * Adds rata_rating and jumlah_ulasan, counting only reviews that are shown.
     */
    #[Scope]
    protected function denganRating(Builder $query): void
    {
        $query->withAvg(['ulasan as rata_rating' => fn (Builder $ulasan) => $ulasan->where('ditampilkan', true)], 'nilai')
            ->withCount(['ulasan as jumlah_ulasan' => fn (Builder $ulasan) => $ulasan->where('ditampilkan', true)]);
    }

    public function isListed(): bool
    {
        return static::query()->tampil()->whereKey($this->getKey())->exists();
    }

    public function isInternal(): bool
    {
        return $this->status === StatusTutor::Internal;
    }

    /**
     * The admin's correction note for one section of the form, if it was flagged.
     */
    public function catatanPerbaikan(LangkahPendaftaran $langkah): ?string
    {
        $bagian = $this->bagian_perbaikan ?? [];

        return array_key_exists($langkah->value, $bagian) ? (string) ($bagian[$langkah->value] ?? '') : null;
    }

    public function perluDiperbaiki(LangkahPendaftaran $langkah): bool
    {
        return $this->catatanPerbaikan($langkah) !== null;
    }

    /**
     * @return BelongsTo<AkunPengguna, $this>
     */
    public function akun(): BelongsTo
    {
        return $this->belongsTo(AkunPengguna::class, 'id_akun', 'id_akun');
    }

    /**
     * @return HasOne<ProfilTutor, $this>
     */
    public function profil(): HasOne
    {
        return $this->hasOne(ProfilTutor::class, 'id_tutor', 'id_tutor');
    }

    /**
     * @return BelongsToMany<MataPelajaran, $this>
     */
    public function mataPelajaran(): BelongsToMany
    {
        return $this->belongsToMany(MataPelajaran::class, 'tutor_mata_pelajaran', 'id_tutor', 'kode_mapel', 'id_tutor', 'kode_mapel');
    }

    /**
     * @return HasMany<DokumenVerifikasi, $this>
     */
    public function dokumen(): HasMany
    {
        return $this->hasMany(DokumenVerifikasi::class, 'id_tutor', 'id_tutor');
    }

    /**
     * @return HasMany<JadwalKetersediaan, $this>
     */
    public function jadwal(): HasMany
    {
        return $this->hasMany(JadwalKetersediaan::class, 'id_tutor', 'id_tutor');
    }

    /**
     * @return HasMany<Pesanan, $this>
     */
    public function pesanan(): HasMany
    {
        return $this->hasMany(Pesanan::class, 'id_tutor', 'id_tutor');
    }

    /**
     * @return HasManyThrough<RatingUlasan, Pesanan, $this>
     */
    public function ulasan(): HasManyThrough
    {
        return $this->hasManyThrough(RatingUlasan::class, Pesanan::class, 'id_tutor', 'id_pesanan', 'id_tutor', 'id_pesanan');
    }

    /**
     * @return HasMany<RiwayatVerifikasiTutor, $this>
     */
    public function riwayatVerifikasi(): HasMany
    {
        return $this->hasMany(RiwayatVerifikasiTutor::class, 'id_tutor', 'id_tutor');
    }

    /**
     * @return HasMany<PencairanDanaTutor, $this>
     */
    public function pencairan(): HasMany
    {
        return $this->hasMany(PencairanDanaTutor::class, 'id_tutor', 'id_tutor');
    }
}
