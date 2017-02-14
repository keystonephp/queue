<?php

declare(strict_types=1);

namespace Keystone\Queue\Middleware;

use Keystone\Queue\Delegate;
use Keystone\Queue\Envelope;
use Keystone\Queue\Message\SimpleMessage;
use Keystone\Queue\Middleware;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Log\NullLogger;

class MaxMessagesMiddlewareTest extends MockeryTestCase
{
    private $middleware;

    public function setUp()
    {
        $this->middleware = new MaxMessagesMiddleware(new NullLogger(), 5);
    }

    public function testBelowMaxMessagesLimit()
    {
        $envelope = new Envelope('test', 'receipt', new SimpleMessage('key', 'body'));
        $delegate = Mockery::mock(Delegate::class, ['process' => true]);

        $this->assertTrue($this->middleware->process($envelope, $delegate));
    }

    public function testExceededMaxMessagesLimit()
    {
        $envelope = new Envelope('test', 'receipt', new SimpleMessage('key', 'body'));
        $delegate = Mockery::mock(Delegate::class, ['process' => true]);

        $this->assertTrue($this->middleware->process($envelope, $delegate));
        $this->assertTrue($this->middleware->process($envelope, $delegate));
        $this->assertTrue($this->middleware->process($envelope, $delegate));
        $this->assertTrue($this->middleware->process($envelope, $delegate));
        $this->assertFalse($this->middleware->process($envelope, $delegate));
    }
}
