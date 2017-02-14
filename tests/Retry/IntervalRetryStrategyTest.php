<?php

declare(strict_types=1);

namespace Keystone\Queue\Retry;

use PHPUnit\Framework\TestCase;

class IntervalRetryStrategyTest extends TestCase
{
    public function testGetDelay()
    {
        $strategy = new IntervalRetryStrategy([10, 20, 30, 40, 50]);
        $this->assertSame(30, $strategy->getDelay(3));
    }

    public function testGetDelayReturnsLastIntervalWhenOutOfRange()
    {
        $strategy = new IntervalRetryStrategy([10, 20, 30, 40, 50]);
        $this->assertSame(50, $strategy->getDelay(10));
    }
}
