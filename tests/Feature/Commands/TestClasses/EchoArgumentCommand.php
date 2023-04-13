<?php

namespace Placetopay\Cerberus\Tests\Feature\Commands\TestClasses;

use Illuminate\Console\Command;

class EchoArgumentCommand extends Command
{
    protected $signature = 'echo:argument {inputString}';

    protected $description = 'Execute an echo command for testing arguments';

    public function handle()
    {
        $this->line($this->argument('inputString'));
    }
}
