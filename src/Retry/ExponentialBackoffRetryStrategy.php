<?php

declare(strict_types=1);

namespace Keystone\Queue\Retry;

class ExponentialBackoffRetryStrategy implements RetryStrategy
{
    /**
     * @param int $attempts
     *
     * @return int
     */
    public function getDelay(int $attempts): int
    {
        return pow($attempts, 4) + 15 + (rand(1, 30) * ($attempts + 1));
    }
}
