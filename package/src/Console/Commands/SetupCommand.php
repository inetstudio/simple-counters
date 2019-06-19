<?php

namespace InetStudio\SimpleCounters\Console\Commands;

use InetStudio\AdminPanel\Base\Console\Commands\BaseSetupCommand;

/**
 * Class SetupCommand.
 */
class SetupCommand extends BaseSetupCommand
{
    /**
     * Имя команды.
     *
     * @var string
     */
    protected $name = 'inetstudio:simple-counters:setup';

    /**
     * Описание команды.
     *
     * @var string
     */
    protected $description = 'Setup simple counters package';

    /**
     * Инициализация команд.
     */
    protected function initCommands(): void
    {
        $this->calls = [
            [
                'type' => 'artisan',
                'description' => 'Meta setup',
                'command' => 'inetstudio:simple-counters:counters:setup',
            ],
        ];
    }
}
