<?php

declare(strict_types=1);

namespace Keystone\Queue\Retry;

interface RetryStrategy
{
    /**
     * @param int $attempts The current number of failed attempts
     *
     * @return int The amount of seconds to wait before retrying
     */
    public function getDelay(int $attempts): int;
}
