<?php

declare(strict_types=1);

namespace Keystone\Queue\Message;

use Keystone\Queue\Message;

/**
 * The message will be retried for the specified maximum number of retries.
 */
interface RetryableMessage extends Message
{
    /**
     * @return int
     */
    public function getMaxRetries(): int;
}
