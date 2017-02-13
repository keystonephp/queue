<?php

declare(strict_types=1);

namespace Keystone\Queue\Middleware;

use Keystone\Queue\Middleware;

/**
 * Middleware implementing this interface will be called when sleeping between fetching messages.
 */
interface SleepyMiddleware extends Middleware
{
    /**
     * @return bool whether to terminate the consumer process
     */
    public function sleep(): bool;
}
