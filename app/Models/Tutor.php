<?php

namespace App\Models;

use App\Enums\StatusTutor;
use App\Enums\StatusVerifikasi;
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

/**
 * @property string $id_tutor
 * @property string|null $id_akun
 * @property string $nama
 * @property StatusTutor $status
 * @property string $kontak
 * @property float|null $rata_rating
 * @property int|null $jumlah_ulasan
 */
#[Fillable(['id_akun', 'nama', 'status', 'kontak'])]
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
        ];
    }

    /**
     * Tutors a parent may find and book: internal tutors, and external tutors
     * whose documents were all approved. Both need a profile.
     */
    #[Scope]
    protected function tampil(Builder $query): void
    {
        $query->whereHas('profil')->where(function (Builder $query): void {
            $query->where('status', StatusTutor::Internal)
                ->orWhere(function (Builder $query): void {
                    $query->whereHas('dokumen')
                        ->whereDoesntHave('dokumen', fn (Builder $dokumen) => $dokumen->where('status_verifikasi', '!=', StatusVerifikasi::Valid));
                });
        });
    }

    /**
     * External tutors with at least one document still waiting for review.
     */
    #[Scope]
    protected function menungguVerifikasi(Builder $query): void
    {
        $query->where('status', StatusTutor::Eksternal)
            ->whereHas('dokumen', fn (Builder $dokumen) => $dokumen->where('status_verifikasi', StatusVerifikasi::Menunggu));
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
     * @return HasMany<PencairanDanaTutor, $this>
     */
    public function pencairan(): HasMany
    {
        return $this->hasMany(PencairanDanaTutor::class, 'id_tutor', 'id_tutor');
    }
}
