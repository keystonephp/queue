<?php

declare(strict_types=1);

namespace Keystone\Queue\Middleware;

use Keystone\Queue\Middleware;

/**
 * Middleware implementing this interface will be called before terminating.
 */
interface TerminableMiddleware extends Middleware
{
    public function terminate();
}
