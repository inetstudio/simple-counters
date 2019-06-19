<?php

namespace InetStudio\SimpleCounters\Counters\Models\Traits;

use ArrayAccess;
use Illuminate\Support\Collection;
use InetStudio\SimpleCounters\Counters\Contracts\Models\CounterModelContract;

/**
 * Trait HasSimpleCountersCollection.
 */
trait HasSimpleCountersCollection
{
    /**
     * Determine if the model has any the given counter.
     *
     * @param  int|string|array|ArrayAccess|CounterModelContract  $counter
     *
     * @return bool
     */
    public function hasCounter($counter): bool
    {
        if ($this->isCounterStringBased($counter)) {
            return ! $this->counters->pluck('type')->intersect((array) $counter)->isEmpty();
        }

        if ($this->isCounterIntBased($counter)) {
            return ! $this->counters->pluck('id')->intersect((array) $counter)->isEmpty();
        }

        if ($counter instanceof CounterModelContract) {
            return $this->counters->contains('type', $counter['type']);
        }

        if ($counter instanceof Collection) {
            return ! $counter->intersect($this->counters->pluck('type'))->isEmpty();
        }

        return false;
    }

    /**
     * Determine if the model has any the given counter.
     *
     * @param  int|string|array|ArrayAccess|CounterModelContract  $counter
     *
     * @return bool
     */
    public function hasAnyCounter($counter): bool
    {
        return $this->hasCounter($counter);
    }

    /**
     * Determine if the model has all of the given counter.
     *
     * @param  int|string|array|ArrayAccess|CounterModelContract  $counters
     *
     * @return bool
     */
    public function hasAllCounters($counters): bool
    {
        if ($this->isCounterStringBased($counters)) {
            $counters = (array) $counters;

            return $this->counters->pluck('type')->intersect($counters)->count() == count($counters);
        }

        if ($this->isCounterIntBased($counters)) {
            $counters = (array) $counters;

            return $this->counters->pluck('id')->intersect($counters)->count() == count($counters);
        }

        if ($counters instanceof CounterModelContract) {
            return $this->counters->contains('type', $counters['type']);
        }

        if ($counters instanceof Collection) {
            return $this->counters->intersect($counters)->count() == $counters->count();
        }

        return false;
    }

    /**
     * Determine if the given counter are string based.
     *
     * @param  int|string|array|ArrayAccess|CounterModelContract  $counter
     *
     * @return bool
     */
    protected function isCounterStringBased($counter): bool
    {
        return is_string($counter) || (is_array($counter) && isset($counter[0]) && is_string($counter[0]));
    }

    /**
     * Determine if the given counter are integer based.
     *
     * @param  int|string|array|ArrayAccess|CounterModelContract  $counter
     *
     * @return bool
     */
    protected function isCounterIntBased($counter): bool
    {
        return is_int($counter) || (is_array($counter) && isset($counter[0]) && is_int($counter[0]));
    }
}
