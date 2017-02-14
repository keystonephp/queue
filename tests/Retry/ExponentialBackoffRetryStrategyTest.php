<?php

declare(strict_types=1);

namespace Keystone\Queue\Retry;

use PHPUnit\Framework\TestCase;

class ExponentialBackoffRetryStrategyTest extends TestCase
{
    public function testGetDelay()
    {
        $strategy = new ExponentialBackoffRetryStrategy();

        $delay = 0;
        for ($attempts = 1; $attempts < 25; ++$attempts) {
            // The delay should keep increasing
            $this->assertGreaterThan($delay, $strategy->getDelay($attempts));
        }
    }
}
