<?php

declare(strict_types=1);

namespace Keystone\Queue\Retry;

class IntervalRetryStrategy implements RetryStrategy
{
    /**
     * @var array
     */
    private $intervals;

    /**
     * @param array $intervals
     */
    public function __construct(array $intervals)
    {
        // e.g. [30, 60, 120, 240]
        $this->intervals = $intervals;
    }

    /**
     * @param int $attempts
     *
     * @return int
     */
    public function getDelay(int $attempts): int
    {
        if ($attempts < 1) {
            $attempts = 1;
        }

        if (array_key_exists($attempts - 1, $this->intervals)) {
            return $this->intervals[$attempts - 1];
        }

        // Return the last interval
        return $this->intervals[count($this->intervals) - 1];
    }
}
