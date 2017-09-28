<?php

namespace InetStudio\SimpleCounters;

use Illuminate\Support\ServiceProvider;

class SimpleCountersServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            if (! class_exists('CreateCountersTables')) {
                $timestamp = date('Y_m_d_His', time());
                $this->publishes([
                    __DIR__.'/../database/migrations/create_simple_counters_tables.php.stub' => database_path('migrations/'.$timestamp.'_create_simple_counters_tables.php'),
                ], 'migrations');
            }

            $this->commands([
                Commands\SetupCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

    }
}
