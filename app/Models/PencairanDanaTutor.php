<?php

namespace App\Models;

use App\Enums\StatusPencairan;
use App\Models\Concerns\HasPrefixedKey;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id_pencairan
 * @property string $id_tutor
 * @property string $periode
 * @property string $nominal
 * @property StatusPencairan $status
 * @property Carbon $created_at
 */
#[Fillable(['periode', 'nominal'])]
class PencairanDanaTutor extends Model
{
    use HasPrefixedKey;

    protected $table = 'pencairan_dana_tutor';

    protected $primaryKey = 'id_pencairan';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $attributes = [
        'status' => 'menunggu',
    ];

    public function keyPrefix(): string
    {
        return 'PC';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => StatusPencairan::class,
            'nominal' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Tutor, $this>
     */
    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class, 'id_tutor', 'id_tutor');
    }
}
