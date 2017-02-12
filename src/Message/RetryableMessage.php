<?php

declare(strict_types=1);

namespace Keystone\Queue\Message;

use Keystone\Queue\Message;

interface RetryableMessage extends Message
{
    /**
     * @return int
     */
    public function getMaxRetries(): int;
}
