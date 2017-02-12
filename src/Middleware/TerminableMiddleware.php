<?php

declare(strict_types=1);

namespace Keystone\Queue\Middleware;

use Keystone\Queue\Middleware;

interface TerminableMiddleware extends Middleware
{
    public function terminate();
}
