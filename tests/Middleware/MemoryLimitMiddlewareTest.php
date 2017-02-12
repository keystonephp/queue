<?php

declare(strict_types=1);

namespace Keystone\Queue\Middleware;

use Keystone\Queue\Delegate;
use Keystone\Queue\Envelope;
use Keystone\Queue\Message\PlainMessage;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class MemoryLimitMiddlewareTest extends TestCase
{
    public function testBelowMemoryLimit()
    {
        $envelope = new Envelope(new PlainMessage('key', 'body'));
        $delegate = Mockery::mock(Delegate::class, ['process' => true]);

        // The memory limit is more than the stubbed usage
        $middleware = new MemoryLimitMiddleware(new NullLogger(), 51);

        $this->assertTrue($middleware->process($envelope, $delegate));
    }

    public function testReachedMemoryLimit()
    {
        $envelope = new Envelope(new PlainMessage('key', 'body'));
        $delegate = Mockery::mock(Delegate::class, ['process' => true]);

        // The memory limit is the same as the stubbed usage
        $middleware = new MemoryLimitMiddleware(new NullLogger(), 50);

        $this->assertFalse($middleware->process($envelope, $delegate));
    }

    public function testExceededMemoryLimit()
    {
        $envelope = new Envelope(new PlainMessage('key', 'body'));
        $delegate = Mockery::mock(Delegate::class, ['process' => true]);

        // The memory limit is lower than the stubbed usage
        $middleware = new MemoryLimitMiddleware(new NullLogger(), 49);

        $this->assertFalse($middleware->process($envelope, $delegate));
    }
}

/**
 * Stub the memory_get_usage function within the current namespace.
 *
 * @return int
 */
function memory_get_usage()
{
    return 52428800; // 50 MB
}
