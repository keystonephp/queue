<?php

declare(strict_types=1);

namespace Keystone\Queue\Middleware;

use Keystone\Queue\Delegate;
use Keystone\Queue\Envelope;
use Keystone\Queue\Message\PlainMessage;
use Keystone\Queue\Middleware;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class MaxExecutionTimeMiddlewareTest extends TestCase
{
    public function testBelowTimeLimit()
    {
        $envelope = new Envelope(new PlainMessage('key', 'body'));
        $delegate = Mockery::mock(Delegate::class, ['process' => true]);

        $middleware = new MaxExecutionTimeMiddleware(new NullLogger(), 10);

        $middleware->initialize();
        $this->assertTrue($middleware->process($envelope, $delegate));
        $this->assertTrue($middleware->process($envelope, $delegate));
    }

    public function testExceededTimeLimit()
    {
        $envelope = new Envelope(new PlainMessage('key', 'body'));
        $delegate = Mockery::mock(Delegate::class, ['process' => true]);

        $middleware = new MaxExecutionTimeMiddleware(new NullLogger(), 0.1);

        $middleware->initialize();
        $this->assertTrue($middleware->process($envelope, $delegate));

        // Wait longer than 0.1 seconds (0.15 seconds)
        usleep(150000);

        // The middleware should not attempt to terminate the consumer
        $this->assertFalse($middleware->process($envelope, $delegate));
    }
}
