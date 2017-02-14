<?php

declare(strict_types=1);

namespace Keystone\Queue\Router;

use Keystone\Queue\Envelope;
use Keystone\Queue\Exception\RoutingException;
use Keystone\Queue\Message\SimpleMessage;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SymfonyContainerRouterTest extends MockeryTestCase
{
    private $container;

    public function setUp()
    {
        $this->container = Mockery::mock(ContainerInterface::class);
    }

    public function testMapReturnsWorker()
    {
        $envelope = new Envelope('test', 'receipt', new SimpleMessage('test', 'body'));
        $router = new SymfonyContainerRouter([SimpleMessage::class => 'worker'], $this->container);

        $this->container->shouldReceive('has')
            ->with('worker')
            ->andReturn(true);

        $worker = new class() {
        };
        $this->container->shouldReceive('get')
            ->with('worker')
            ->andReturn($worker);

        $this->assertSame($worker, $router->map($envelope));
    }

    public function testThrowsExceptionWhenServiceNotDefined()
    {
        $this->setExpectedException(RoutingException::class);

        $envelope = new Envelope('test', 'receipt', new SimpleMessage('test', 'body'));
        $router = new SymfonyContainerRouter([], $this->container);

        $router->map($envelope);
    }

    public function testThrowsExceptionWhenServiceDoesNotExist()
    {
        $this->setExpectedException(RoutingException::class);

        $envelope = new Envelope('test', 'receipt', new SimpleMessage('test', 'body'));
        $router = new SymfonyContainerRouter([SimpleMessage::class => 'worker'], $this->container);

        $this->container->shouldReceive('has')
            ->with('worker')
            ->andReturn(false);

        $router->map($envelope);
    }
}
