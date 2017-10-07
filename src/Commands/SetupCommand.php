<?php

namespace InetStudio\SimpleCounters\Commands;

use Illuminate\Console\Command;

class SetupCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'inetstudio:counters:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup counters package';

    /**
     * Commands to call with their description.
     *
     * @var array
     */
    protected $calls = [];

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->initCommands();

        foreach ($this->calls as $info) {
            if (! isset($info['command'])) {
                continue;
            }

            $this->line(PHP_EOL.$info['description']);
            $this->call($info['command'], $info['params']);
        }
    }

    /**
     * Инициализация команд.
     *
     * @return void
     */
    private function initCommands()
    {
        $this->calls = [
            [
                'description' => 'Publish migrations',
                'command' => 'vendor:publish',
                'params' => [
                    '--provider' => 'InetStudio\SimpleCounters\SimpleCountersServiceProvider',
                    '--tag' => 'migrations',
                ],
            ],
            [
                'description' => 'Migration',
                'command' => 'migrate',
                'params' => [],
            ],
            [
                'description' => 'Publish config',
                'command' => 'vendor:publish',
                'params' => [
                    '--provider' => 'InetStudio\SimpleCounters\SimpleCountersServiceProvider',
                    '--tag' => 'config',
                ],
            ],
        ];
    }
}
