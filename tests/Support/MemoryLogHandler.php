<?php

namespace Placetopay\Cerberus\Tests\Support;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

class MemoryLogHandler extends AbstractProcessingHandler
{
    public array $records = [];

    protected function write(LogRecord $record): void
    {
        $this->records[] = $record;
    }
}
