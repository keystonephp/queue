<?php

declare(strict_types=1);

namespace Keystone\Queue\Exception;

use Exception;

/**
 * Raised when a message could not be routed to a worker.
 */
class RoutingException extends Exception
{
}
