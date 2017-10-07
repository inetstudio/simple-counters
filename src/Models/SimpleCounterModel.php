<?php

namespace InetStudio\SimpleCounters\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SimpleCounterModel extends Model
{
    use SoftDeletes;

    /**
     * Связанная с моделью таблица.
     *
     * @var string
     */
    protected $table = 'simple_counters';

    /**
     * Атрибуты, для которых разрешено массовое назначение.
     *
     * @var array
     */
    protected $fillable = [
        'countable_id', 'countable_type', 'type', 'counter',
    ];

    /**
     * Атрибуты, которые должны быть преобразованы в даты.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Получаем связанную модель.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function countable()
    {
        return $this->morphTo();
    }
}
