<?php

declare(strict_types=1);

namespace Keystone\Queue\Middleware;

use Keystone\Queue\Middleware;

/**
 * Middleware implementing this interface will be called when initializing the consumer.
 */
interface InitializableMiddleware extends Middleware
{
    public function initialize();
}
