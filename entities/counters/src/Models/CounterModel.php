<?php

namespace InetStudio\SimpleCounters\Counters\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use InetStudio\AdminPanel\Base\Models\Traits\Scopes\BuildQueryScopeTrait;
use InetStudio\SimpleCounters\Counters\Contracts\Models\CounterModelContract;

/**
 * Class CounterModel.
 */
class CounterModel extends Model implements CounterModelContract
{
    use SoftDeletes;
    use BuildQueryScopeTrait;

    /**
     * Тип сущности.
     */
    const ENTITY_TYPE = 'simple_counter';

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
        'countable_id',
        'countable_type',
        'type',
        'counter',
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
     * Загрузка модели.
     */
    protected static function boot()
    {
        parent::boot();

        self::$buildQueryScopeDefaults['columns'] = [
            'id',
            'countable_id',
            'countable_type',
            'type',
            'counter',
        ];
    }

    /**
     * Сеттер атрибута countable_type.
     *
     * @param $value
     */
    public function setCountableTypeAttribute($value)
    {
        $this->attributes['countable_type'] = trim(strip_tags($value));
    }

    /**
     * Сеттер атрибута countable_id.
     *
     * @param $value
     */
    public function setCountableIdAttribute($value)
    {
        $this->attributes['countable_id'] = (int) trim(strip_tags($value));
    }

    /**
     * Сеттер атрибута type.
     *
     * @param $value
     */
    public function setTypeAttribute($value): void
    {
        $this->attributes['type'] = trim(strip_tags($value));
    }

    /**
     * Сеттер атрибута counter.
     *
     * @param $value
     */
    public function setCounterAttribute($value): void
    {
        $this->attributes['counter'] = (int) trim(strip_tags($value));
    }

    /**
     * Геттер атрибута type.
     *
     * @return string
     */
    public function getTypeAttribute(): string
    {
        return self::ENTITY_TYPE;
    }


    /**
     * Полиморфное отношение с остальными моделями.
     *
     * @return MorphTo
     */
    public function countable(): MorphTo
    {
        return $this->morphTo();
    }
}
