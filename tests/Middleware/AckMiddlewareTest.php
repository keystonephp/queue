<?php

declare(strict_types=1);

namespace Keystone\Queue\Middleware;

use Exception;
use Keystone\Queue\Delegate;
use Keystone\Queue\Envelope;
use Keystone\Queue\Message\SimpleMessage;
use Keystone\Queue\Middleware;
use Keystone\Queue\Provider;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Log\NullLogger;
use Throwable;

class AckMiddlewareTest extends MockeryTestCase
{
    private $provider;
    private $middleware;

    public function setUp()
    {
        $this->provider = Mockery::mock(Provider::class);
        $this->middleware = new AckMiddleware($this->provider, new NullLogger());
    }

    public function testAcksEnvelope()
    {
        $envelope = new Envelope('test', 'receipt', new SimpleMessage('key', 'body'));
        $delegate = Mockery::mock(Delegate::class);
        $delegate->shouldReceive('process')
            ->andReturn(true);

        $this->provider->shouldReceive('ack')
            ->once()
            ->with($envelope);

        $this->assertTrue($this->middleware->process($envelope, $delegate));
    }

    public function testCatchesExceptionsAndNacksEnvelope()
    {
        $this->setExpectedException(Exception::class);

        $envelope = new Envelope('test', 'receipt', new SimpleMessage('key', 'body'));
        $delegate = Mockery::mock(Delegate::class);
        $delegate->shouldReceive('process')
            ->andReturnUsing(function () {
                throw new Exception();
            });

        $this->provider->shouldReceive('nack')
            ->once()
            ->with($envelope);

        $this->assertTrue($this->middleware->process($envelope, $delegate));
    }

    public function testCatchesThrowablesAndNacksEnvelope()
    {
        $this->setExpectedException(Throwable::class);

        $envelope = new Envelope('test', 'receipt', new SimpleMessage('key', 'body'));
        $delegate = Mockery::mock(Delegate::class);
        $delegate->shouldReceive('process')
            ->andReturnUsing(function () {
                new ThisClassDoesNotExistAndWillCauseAnError();
            });

        $this->provider->shouldReceive('nack')
            ->once()
            ->with($envelope);

        $this->assertTrue($this->middleware->process($envelope, $delegate));
    }
}
