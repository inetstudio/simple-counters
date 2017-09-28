<?php

namespace InetStudio\SimpleCounters\Traits;

use InetStudio\SimpleCounters\Models\SimpleCounterModel;

trait HasSimpleCountersTrait
{
    public static function getSimpleCounterClassName()
    {
        return SimpleCounterModel::class;
    }

    public function counters()
    {
        return $this->morphMany(static::getSimpleCounterClassName(), 'countable');
    }

    public function incrementCount($type = '')
    {
        if (config('counters.enabled')) {

            if (config('counters.availableTypes') && ! in_array($type, config('counters.availableTypes'))) {
                return;
            }

            if ($this->counters()->where('type', $type)->count() == 0) {

                $counter = new SimpleCounterModel();

                $counter->type = $type;
                $counter->counter = 1;

                $this->counters()->save($counter);
            } else {
                $this->counters()->where('type', $type)->increment('counter');
            }
        }
    }

    public function decrementCount($type = '')
    {
        if (config('counters.enabled')) {

            if (config('counters.availableTypes') && ! in_array($type, config('counters.availableTypes'))) {
                return;
            }
            
            if ($this->counters()->where('type', $type)->count() == 0) {
                return;
            } else {
                $this->counters()->where('type', $type)->decrement('counter');
            }
        }
    }

    public function addView()
    {
        $this->incrementCount('view');
    }
}
