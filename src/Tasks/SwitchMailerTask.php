<?php

namespace Placetopay\Cerberus\Tasks;

use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

class SwitchMailerTask implements SwitchTenantTask
{
    private string $originalDrive;

    public function __construct()
    {
        $this->originalDrive ??= config('mail.default');
    }

    public function makeCurrent(IsTenant $tenant): void
    {
        app('mail.manager')->forgetMailers();
    }

    public function forgetCurrent(): void
    {
        app('mail.manager')->forgetMailers();
    }
}
