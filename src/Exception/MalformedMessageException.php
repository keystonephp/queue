<?php

declare(strict_types=1);

namespace Keystone\Queue\Exception;

use UnexpectedValueException;

/**
 * Raised when a message could not be unserialized.
 */
class MalformedMessageException extends UnexpectedValueException
{
}
