<?php

namespace InetStudio\SimpleCounters\Counters\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

/**
 * Class ServiceProvider.
 */
class ServiceProvider extends BaseServiceProvider
{
    /**
     * Загрузка сервиса.
     */
    public function boot(): void
    {
        $this->registerConsoleCommands();
        $this->registerPublishes();
    }

    /**
     * Регистрация команд.
     */
    protected function registerConsoleCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            'InetStudio\SimpleCounters\Counters\Console\Commands\SetupCommand',
        ]);
    }

    /**
     * Регистрация ресурсов.
     *
     * @return void
     */
    protected function registerPublishes(): void
    {
        $this->publishes([
            __DIR__.'/../../config/counters.php' => config_path('counters.php'),
        ], 'config');

        if (! $this->app->runningInConsole()) {
            return;
        }

        if (Schema::hasTable('simple_counters')) {
            return;
        }

        $timestamp = date('Y_m_d_His', time());
        $this->publishes(
            [
                __DIR__.'/../../database/migrations/create_simple_counters_tables.php.stub' => database_path(
                    'migrations/'.$timestamp.'_create_simple_counters_tables.php'
                ),
            ],
            'migrations'
        );
    }
}
