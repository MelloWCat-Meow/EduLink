<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * String primary keys in the report's format: a prefix plus a zero-padded
 * sequence number, e.g. AK0001 or S0001. The model defines keyPrefix().
 *
 * @mixin Model
 */
trait HasPrefixedKey
{
    /**
     * Digits after the prefix; the sequence keeps counting past 9999.
     */
    protected int $keyDigits = 4;

    /**
     * The fixed text in front of every key, e.g. "AK".
     */
    abstract public function keyPrefix(): string;

    public static function bootHasPrefixedKey(): void
    {
        static::creating(function (Model $model): void {
            if (blank($model->getKey())) {
                $model->setAttribute($model->getKeyName(), $model->nextKey());
            }
        });
    }

    /**
     * The key after the highest existing one. The read is locked so callers that
     * create inside a transaction do not hand out the same key twice (MySQL).
     */
    public function nextKey(): string
    {
        $prefix = $this->keyPrefix();
        $column = $this->getKeyName();

        $lastKey = static::query()
            ->where($column, 'like', $prefix.'%')
            ->orderByRaw("LENGTH({$column}) DESC")
            ->orderByDesc($column)
            ->lockForUpdate()
            ->value($column);

        $nextNumber = $lastKey === null ? 1 : ((int) substr($lastKey, strlen($prefix))) + 1;

        return $prefix.str_pad((string) $nextNumber, $this->keyDigits, '0', STR_PAD_LEFT);
    }
}
