<?php

declare(strict_types=1);

namespace Keystone\Queue\Middleware;

use Keystone\Queue\Middleware;

interface InitializableMiddleware extends Middleware
{
    public function initialize();
}
