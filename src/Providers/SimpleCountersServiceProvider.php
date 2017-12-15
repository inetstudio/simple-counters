<?php

namespace InetStudio\SimpleCounters\Providers;

use Illuminate\Support\ServiceProvider;
use InetStudio\SimpleCounters\Console\Commands\SetupCommand;

class SimpleCountersServiceProvider extends ServiceProvider
{
    /**
     * Загрузка сервиса.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerConsoleCommands();
        $this->registerPublishes();
    }

    /**
     * Регистрация привязки в контейнере.
     *
     * @return void
     */
    public function register(): void
    {

    }

    /**
     * Регистрация команд.
     *
     * @return void
     */
    protected function registerConsoleCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SetupCommand::class,
            ]);
        }
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

        if ($this->app->runningInConsole()) {
            if (! class_exists('CreateCountersTables')) {
                $timestamp = date('Y_m_d_His', time());
                $this->publishes([
                    __DIR__.'/../../database/migrations/create_simple_counters_tables.php.stub' => database_path('migrations/'.$timestamp.'_create_simple_counters_tables.php'),
                ], 'migrations');
            }
        }
    }
}
