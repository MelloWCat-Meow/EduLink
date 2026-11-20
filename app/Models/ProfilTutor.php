<?php

namespace App\Models;

use App\Enums\ModeBelajar;
use App\Models\Concerns\HasPrefixedKey;
use Database\Factories\ProfilTutorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id_profil
 * @property string $id_tutor
 * @property array{pendidikan?: string, institusi?: string, jurusan?: string}|null $riwayat_pendidikan
 * @property string $kompetensi
 * @property string $pengalaman
 * @property string|null $tarif
 * @property string|null $lokasi
 * @property string|null $foto
 * @property bool $melayani_online
 * @property bool $melayani_kunjungan
 */
#[Fillable(['riwayat_pendidikan', 'kompetensi', 'pengalaman', 'tarif', 'lokasi', 'foto', 'melayani_online', 'melayani_kunjungan'])]
class ProfilTutor extends Model
{
    /** @use HasFactory<ProfilTutorFactory> */
    use HasFactory, HasPrefixedKey;

    protected $table = 'profil_tutor';

    protected $primaryKey = 'id_profil';

    public $incrementing = false;

    protected $keyType = 'string';

    public function keyPrefix(): string
    {
        return 'PR';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'riwayat_pendidikan' => 'array',
            'tarif' => 'decimal:2',
            'melayani_online' => 'boolean',
            'melayani_kunjungan' => 'boolean',
        ];
    }

    /**
     * @return list<ModeBelajar>
     */
    public function modes(): array
    {
        return array_values(array_filter([
            $this->melayani_online ? ModeBelajar::Online : null,
            $this->melayani_kunjungan ? ModeBelajar::KunjunganRumah : null,
        ]));
    }

    /**
     * "Online atau datang ke rumah", for one line of a profile card.
     */
    public function modesLabel(): string
    {
        $bagian = array_map(fn (ModeBelajar $mode): string => $mode === ModeBelajar::Online ? 'online' : 'datang ke rumah', $this->modes());

        return ucfirst(implode(' atau ', $bagian));
    }

    /**
     * "S1 Pendidikan Matematika, Universitas Jambi".
     */
    public function pendidikanLabel(): ?string
    {
        $riwayat = $this->riwayat_pendidikan ?? [];

        if (blank($riwayat['pendidikan'] ?? null)) {
            return null;
        }

        return trim(($riwayat['pendidikan'] ?? '').' '.($riwayat['jurusan'] ?? '')).(filled($riwayat['institusi'] ?? null) ? ', '.$riwayat['institusi'] : '');
    }

    public function offers(ModeBelajar $mode): bool
    {
        return in_array($mode, $this->modes(), true);
    }

    /**
     * @return BelongsTo<Tutor, $this>
     */
    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class, 'id_tutor', 'id_tutor');
    }
}
