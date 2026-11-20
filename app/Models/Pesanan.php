<?php

namespace App\Models;

use App\Enums\ModeBelajar;
use App\Enums\StatusPesanan;
use App\Models\Concerns\HasPrefixedKey;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * An order for a series of weekly sessions. Its status only changes through
 * App\Services\AlurPesanan.
 *
 * @property string $id_pesanan
 * @property string $id_siswa
 * @property string $id_tutor
 * @property string $kode_mapel
 * @property string $id_jadwal
 * @property int $durasi
 * @property ModeBelajar $mode
 * @property StatusPesanan $status
 * @property Carbon $tanggal_mulai
 * @property int $jumlah_pertemuan
 * @property string $tarif_per_jam
 * @property string $total_biaya
 * @property Carbon|null $ditahan_hingga
 * @property Carbon $created_at
 */
#[Fillable(['id_siswa', 'id_tutor', 'kode_mapel', 'id_jadwal', 'durasi', 'mode', 'tanggal_mulai', 'jumlah_pertemuan', 'tarif_per_jam', 'total_biaya'])]
class Pesanan extends Model
{
    use HasPrefixedKey;

    protected $table = 'pesanan';

    protected $primaryKey = 'id_pesanan';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * The report's order number format: ORD-2026-000045.
     */
    public function keyPrefix(): string
    {
        return 'ORD-'.now()->year.'-';
    }

    public function keyDigits(): int
    {
        return 6;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => StatusPesanan::class,
            'mode' => ModeBelajar::class,
            'tanggal_mulai' => 'date',
            'ditahan_hingga' => 'datetime',
            'durasi' => 'integer',
            'jumlah_pertemuan' => 'integer',
            'tarif_per_jam' => 'decimal:2',
            'total_biaya' => 'decimal:2',
        ];
    }

    /**
     * Orders for the children of one guardian account.
     */
    #[Scope]
    protected function milikAkun(Builder $query, string $idAkun): void
    {
        $query->whereIn('id_siswa', Siswa::query()->select('id_siswa')->where('id_akun', $idAkun));
    }

    public function isHoldExpired(): bool
    {
        return $this->status->holdsSlot() && $this->ditahan_hingga !== null && $this->ditahan_hingga->isPast();
    }

    /**
     * @return BelongsTo<Siswa, $this>
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }

    /**
     * @return BelongsTo<Tutor, $this>
     */
    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class, 'id_tutor', 'id_tutor');
    }

    /**
     * @return BelongsTo<MataPelajaran, $this>
     */
    public function mapel(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'kode_mapel', 'kode_mapel');
    }

    /**
     * @return BelongsTo<JadwalKetersediaan, $this>
     */
    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(JadwalKetersediaan::class, 'id_jadwal', 'id_jadwal');
    }

    /**
     * @return HasMany<SesiPembelajaran, $this>
     */
    public function sesi(): HasMany
    {
        return $this->hasMany(SesiPembelajaran::class, 'id_pesanan', 'id_pesanan');
    }

    /**
     * @return HasMany<Pembayaran, $this>
     */
    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'id_pesanan', 'id_pesanan');
    }

    /**
     * @return HasOne<KomisiBiayaLayanan, $this>
     */
    public function komisi(): HasOne
    {
        return $this->hasOne(KomisiBiayaLayanan::class, 'id_pesanan', 'id_pesanan');
    }

    /**
     * @return HasOne<RatingUlasan, $this>
     */
    public function ulasan(): HasOne
    {
        return $this->hasOne(RatingUlasan::class, 'id_pesanan', 'id_pesanan');
    }

    /**
     * @return HasMany<Keluhan, $this>
     */
    public function keluhan(): HasMany
    {
        return $this->hasMany(Keluhan::class, 'id_pesanan', 'id_pesanan');
    }

    /**
     * @return HasMany<RiwayatStatusPesanan, $this>
     */
    public function riwayat(): HasMany
    {
        return $this->hasMany(RiwayatStatusPesanan::class, 'id_pesanan', 'id_pesanan');
    }
}
