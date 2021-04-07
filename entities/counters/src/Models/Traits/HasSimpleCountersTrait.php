<?php

namespace InetStudio\SimpleCounters\Counters\Models\Traits;

use ArrayAccess;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Contracts\Container\BindingResolutionException;
use InetStudio\MetaPackage\Meta\Contracts\Models\MetaModelContract;
use InetStudio\SimpleCounters\Counters\Contracts\Models\CounterModelContract;

/**
 * Trait HasSimpleCountersTrait.
 */
trait HasSimpleCountersTrait
{
    use HasSimpleCountersCollection;

    /**
     * Get counter class name.
     *
     * @return string
     *
     * @throws BindingResolutionException
     */
    public function getCounterClassName(): string
    {
        $model = app()->make(CounterModelContract::class);

        return get_class($model);
    }

    /**
     * Получаем все счетчики материала.
     *
     * @return MorphMany
     *
     * @throws BindingResolutionException
     */
    public function counters(): MorphMany
    {
        $className = $this->getCounterClassName();

        return $this->morphMany($className, 'countable');
    }

    /**
     * Boot the HasSimpleCounters trait for a model.
     */
    public static function bootHasSimpleCounters()
    {
        static::deleted(
            function (Model $countableModel) {
                $countableModel->counters()->delete();
            }
        );
    }

    /**
     * Get the counters list.
     *
     * @return array
     *
     * @throws BindingResolutionException
     */
    public function getCountersList(): array
    {
        return $this->counters()
            ->pluck('counter', 'type')
            ->toArray();
    }

    /**
     * Получаем мета тег.
     *
     * @param  string  $type
     * @param $default
     * @param  bool  $returnObject
     *
     * @return mixed|null
     *
     * @throws BindingResolutionException
     */
    public function getCounter(string $type, $default = 0, bool $returnObject = false)
    {
        $builder = $this->counters()
            ->where('type', $type);

        if ($returnObject) {
            return $builder->withTrashed()->first();
        } else {
            $counter = $builder->first();
        }

        return ($counter) ? $counter->counter : $default;
    }

    /**
     * Scope query with all the given counters.
     *
     * @param  Builder  $query
     * @param  int|string|array|ArrayAccess|CounterModelContract  $counters
     *
     * @return Builder
     *
     * @throws BindingResolutionException
     */
    public function scopeWithAllCounters(Builder $query, $counters): Builder
    {
        $counters = $this->isCounterStringBased($counters)
            ? $counters : $this->hydrateCounters($counters)->pluck('type')->toArray();

        collect($counters)->each(
            function ($counterItem) use ($query) {
                $query->whereHas(
                    'counters',
                    function (Builder $query) use ($counterItem) {
                        return $query->where('type', $counterItem);
                    }
                );
            }
        );

        return $query;
    }

    /**
     * Scope query with any of the given counters.
     *
     * @param  Builder  $query
     * @param  int|string|array|ArrayAccess|CounterModelContract  $counters
     *
     * @return Builder
     *
     * @throws BindingResolutionException
     */
    public function scopeWithAnyCounters(Builder $query, $counters): Builder
    {
        $counters = $this->isCounterStringBased($counters)
            ? $counters : $this->hydrateCounters($counters)->pluck('key')->toArray();

        return $query->whereHas(
            'counters',
            function (Builder $query) use ($counters) {
                $query->whereIn('type', (array) $counters);
            }
        );
    }

    /**
     * Scope query with any of the given counters.
     *
     * @param  Builder  $query
     * @param  int|string|array|ArrayAccess|CounterModelContract  $counters
     *
     * @return Builder
     *
     * @throws BindingResolutionException
     */
    public function scopeWithCounters(Builder $query, $counters): Builder
    {
        return $this->scopeWithAnyCounters($query, $counters);
    }

    /**
     * Scope query without the given counter.
     *
     * @param  Builder  $query
     * @param  int|string|array|ArrayAccess|CounterModelContract  $counter
     *
     * @return Builder
     *
     * @throws BindingResolutionException
     */
    public function scopeWithoutCounter(Builder $query, $counter): Builder
    {
        $counter = $this->isCounterStringBased($counter)
            ? $counter : $this->hydrateCounters($counter)->pluck('type')->toArray();

        return $query->whereDoesntHave(
            'counters',
            function (Builder $query) use ($counter) {
                $query->whereIn('type', (array) $counter);
            }
        );
    }

    /**
     * Scope query without any counter.
     *
     * @param  Builder  $query
     *
     * @return Builder
     */
    public function scopeWithoutAnyCounter(Builder $query): Builder
    {
        return $query->doesntHave('counters');
    }

    /**
     * Detach the given meta from the model.
     *
     * @param  int|string|array|ArrayAccess|MetaModelContract  $types
     *
     * @return $this
     *
     * @throws BindingResolutionException
     */
    public function detachCounters($types): self
    {
        static::$dispatcher->dispatch('inetstudio.counters.detaching', [$this, $types]);

        foreach ($types ?? [] as $type) {
            $this->deleteCounter($type);
        }

        static::$dispatcher->dispatch('inetstudio.counters.detached', [$this, $types]);

        return $this;
    }

    /**
     * Hydrate counters.
     *
     * @param  int|string|array|ArrayAccess|CounterModelContract  $counters
     *
     * @return Collection
     *
     * @throws BindingResolutionException
     */
    protected function hydrateCounters($counters): Collection
    {
        $isCounterStringBased = $this->isCounterStringBased($counters);
        $isCounterIntBased = $this->isCounterIntBased($counters);
        $field = $isCounterStringBased ? 'type' : 'id';
        $className = $this->getCounterClassName();

        return $isCounterStringBased || $isCounterIntBased
            ? $className::query()->whereIn($field, (array) $counters)->get() : collect($counters);
    }

    /**
     * Удаляем счетчик.
     *
     * @param  string  $type
     *
     * @return mixed
     *
     * @throws BindingResolutionException
     */
    protected function deleteCounter($type)
    {
        return $this->counters()
            ->where('type', $type)
            ->delete();
    }

    /**
     * Инкрементируем счетчик.
     *
     * @param  string  $type
     * @param  int  $amount
     *
     * @return $this
     *
     * @throws BindingResolutionException
     */
    public function incrementCounter(string $type, int $amount = 1): self
    {
        if (! in_array($type, config('counters.availableTypes', []))) {
            return $this;
        }

        if (! $this->counters()->where('type', $type)->first()) {
            $this->counters()->create(
                [
                    'type' => $type,
                    'counter' => $amount,
                ]
            );
        } else {
            $this->counters()
                ->where('type', $type)
                ->increment('counter', $amount);
        }

        return $this;
    }

    /**
     * Декрементируем счетчик.
     *
     * @param  string  $type
     * @param  int  $amount
     *
     * @return $this
     *
     * @throws BindingResolutionException
     */
    public function decrementCounter(string $type, int $amount = 1): self
    {
        if (! in_array($type, config('counters.availableTypes', []))) {
            return $this;
        }

        if (! $this->counters()->where('type', $type)->first()) {
            return $this;
        }

        $this->counters()->where('type', $type)->decrement('counter', $amount);

        return $this;
    }

    /**
     * Сбрасывыем счетчик.
     *
     * @param  string  $type
     *
     * @return $this
     *
     * @throws BindingResolutionException
     */
    public function resetCounter(string $type): self
    {
        if (! in_array($type, config('counters.availableTypes', []))) {
            return $this;
        }

        if (! $this->counters()->where('type', $type)->first()) {
            return $this;
        }

        $this->counters()->where('type', $type)->update(
            [
                'counter' => 0,
            ]
        );

        return $this;
    }
}
