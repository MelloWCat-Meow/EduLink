<?php

namespace App\Models;

use App\Enums\Hari;
use App\Enums\StatusJadwal;
use App\Models\Concerns\HasPrefixedKey;
use Database\Factories\JadwalKetersediaanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A weekly slot: the same day and start time every week.
 *
 * @property string $id_jadwal
 * @property string $id_tutor
 * @property Hari $hari
 * @property string $jam
 * @property StatusJadwal $status
 */
#[Fillable(['hari', 'jam', 'status'])]
class JadwalKetersediaan extends Model
{
    /** @use HasFactory<JadwalKetersediaanFactory> */
    use HasFactory, HasPrefixedKey;

    protected $table = 'jadwal_ketersediaan';

    protected $primaryKey = 'id_jadwal';

    public $incrementing = false;

    protected $keyType = 'string';

    public function keyPrefix(): string
    {
        return 'JD';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hari' => Hari::class,
            'status' => StatusJadwal::class,
        ];
    }

    /**
     * Start time as "16.00", the Indonesian way of writing a time.
     */
    public function jamLabel(): string
    {
        return str_replace(':', '.', substr($this->jam, 0, 5));
    }

    public function label(): string
    {
        return $this->hari->label().', '.$this->jamLabel();
    }

    /**
     * @return BelongsTo<Tutor, $this>
     */
    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class, 'id_tutor', 'id_tutor');
    }
}
