<?php

declare(strict_types=1);

namespace Keystone\Queue\Middleware;

use Keystone\Queue\Delegate;
use Keystone\Queue\Envelope;
use Keystone\Queue\Message\SimpleMessage;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Log\NullLogger;

class MemoryLimitMiddlewareTest extends MockeryTestCase
{
    public function testBelowMemoryLimit()
    {
        $envelope = new Envelope('test', 'receipt', new SimpleMessage('key', 'body'));
        $delegate = Mockery::mock(Delegate::class, ['process' => true]);

        // The memory limit is more than the stubbed usage
        $middleware = new MemoryLimitMiddleware(new NullLogger(), 51);

        $this->assertTrue($middleware->process($envelope, $delegate));
    }

    public function testReachedMemoryLimit()
    {
        $envelope = new Envelope('test', 'receipt', new SimpleMessage('key', 'body'));
        $delegate = Mockery::mock(Delegate::class, ['process' => true]);

        // The memory limit is the same as the stubbed usage
        $middleware = new MemoryLimitMiddleware(new NullLogger(), 50);

        $this->assertFalse($middleware->process($envelope, $delegate));
    }

    public function testExceededMemoryLimit()
    {
        $envelope = new Envelope('test', 'receipt', new SimpleMessage('key', 'body'));
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
