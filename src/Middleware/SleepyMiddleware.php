<?php

declare(strict_types=1);

namespace Keystone\Queue\Middleware;

use Keystone\Queue\Middleware;

interface SleepyMiddleware extends Middleware
{
    public function sleep(): bool;
}
