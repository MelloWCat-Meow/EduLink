<?php

namespace App\Models;

use App\Enums\Kehadiran;
use App\Enums\StatusKonfirmasiSesi;
use App\Models\Concerns\HasPrefixedKey;
use App\Services\KonfirmasiSesi;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id_sesi
 * @property string $id_pesanan
 * @property Carbon $waktu
 * @property Kehadiran|null $kehadiran
 * @property string|null $catatan
 * @property string|null $tautan_meeting the tutor's link for this session only; overrides the order's
 * @property Carbon|null $pengingat_dikirim_pada
 * @property StatusKonfirmasiSesi|null $status_konfirmasi
 * @property Carbon|null $dicatat_pada
 * @property Carbon|null $dikonfirmasi_pada
 * @property string|null $dikonfirmasi_oleh ortu, sistem or admin
 * @property string|null $alasan_sanggahan
 * @property string|null $keputusan_admin
 * @property string|null $id_pengembalian
 */
#[Fillable(['waktu', 'kehadiran', 'catatan', 'tautan_meeting'])]
class SesiPembelajaran extends Model
{
    use HasPrefixedKey;

    protected $table = 'sesi_pembelajaran';

    protected $primaryKey = 'id_sesi';

    public $incrementing = false;

    protected $keyType = 'string';

    public function keyPrefix(): string
    {
        return 'SES';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'waktu' => 'datetime',
            'kehadiran' => Kehadiran::class,
            'pengingat_dikirim_pada' => 'datetime',
            'status_konfirmasi' => StatusKonfirmasiSesi::class,
            'dicatat_pada' => 'datetime',
            'dikonfirmasi_pada' => 'datetime',
        ];
    }

    /**
     * Sessions of one tutor's paid orders, the ones that can still be logged.
     */
    #[Scope]
    protected function milikTutor(Builder $query, string $idTutor): void
    {
        $query->whereHas('pesanan', fn (Builder $pesanan) => $pesanan->where('id_tutor', $idTutor));
    }

    /**
     * The meeting link that applies: this session's own, else the order's
     * default. Needs the pesanan relation (or the order passed in).
     */
    public function tautanBerlaku(?Pesanan $pesanan = null): ?string
    {
        return $this->tautan_meeting ?? ($pesanan ?? $this->pesanan)?->tautan_meeting;
    }

    /**
     * When the parent's 48-hour window to confirm or dispute ends.
     */
    public function batasKonfirmasi(): ?Carbon
    {
        return $this->dicatat_pada?->copy()->addHours(KonfirmasiSesi::BATAS_JAM);
    }

    /**
     * @return HasMany<PermintaanJadwalUlang, $this>
     */
    public function permintaanJadwalUlang(): HasMany
    {
        return $this->hasMany(PermintaanJadwalUlang::class, 'id_sesi', 'id_sesi');
    }

    /**
     * @return BelongsTo<Pesanan, $this>
     */
    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan', 'id_pesanan');
    }
}
