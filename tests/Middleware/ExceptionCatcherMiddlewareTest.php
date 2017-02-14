<?php

declare(strict_types=1);

namespace Keystone\Queue\Middleware;

use Exception;
use Keystone\Queue\Delegate;
use Keystone\Queue\Envelope;
use Keystone\Queue\Message\SimpleMessage;
use Keystone\Queue\Middleware;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Log\NullLogger;

class ExceptionCatcherMiddlewareTest extends MockeryTestCase
{
    private $middleware;

    public function setUp()
    {
        $this->middleware = new ExceptionCatcherMiddleware(new NullLogger());
    }

    public function testCatchesExceptions()
    {
        $envelope = new Envelope('test', 'receipt', new SimpleMessage('key', 'body'));
        $delegate = Mockery::mock(Delegate::class);
        $delegate->shouldReceive('process')
            ->andReturnUsing(function () {
                throw new Exception();
            });

        $this->assertTrue($this->middleware->process($envelope, $delegate));
    }

    public function testCatchesThrowables()
    {
        $envelope = new Envelope('test', 'receipt', new SimpleMessage('key', 'body'));
        $delegate = Mockery::mock(Delegate::class);
        $delegate->shouldReceive('process')
            ->andReturnUsing(function () {
                new ThisClassDoesNotExistAndWillCauseAnError();
            });

        $this->assertTrue($this->middleware->process($envelope, $delegate));
    }
}
