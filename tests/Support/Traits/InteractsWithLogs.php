<?php

namespace Placetopay\Cerberus\Tests\Support\Traits;

use Illuminate\Support\Facades\Log;
use Monolog\Handler\TestHandler;

trait InteractsWithLogs
{
    protected TestHandler $testLogHandler;

    protected function fakeLogs(): void
    {
        $this->testLogHandler = new TestHandler();
        Log::getLogger()->pushHandler($this->testLogHandler);
    }

    protected function assertLogMessageContains(string $substring): void
    {
        $logFound = collect($this->testLogHandler->getRecords())->contains(function ($record) use ($substring) {
            return str_contains($record['message'], $substring);
        });

        $this->assertTrue($logFound, "Expected log message containing '{$substring}' not found.");
    }

    protected function getAllLogMessages(): array
    {
        return array_map(fn ($record) => $record['message'], $this->testLogHandler->getRecords());
    }
}
