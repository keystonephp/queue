<?php

declare(strict_types=1);

namespace Keystone\Queue\Router;

use Keystone\Queue\Envelope;
use Keystone\Queue\Exception\RoutingException;
use Keystone\Queue\Message\SimpleMessage;
use Mockery;
use PHPUnit\Framework\TestCase;

class SimpleRouterTest extends TestCase
{
    public function testMapReturnsWorker()
    {
        $envelope = new Envelope('test', new SimpleMessage('test', 'body'));

        $worker = Mockery::mock();
        $router = new SimpleRouter([SimpleMessage::class => $worker]);

        $this->assertSame($worker, $router->map($envelope));
    }

    public function testThrowsExceptionWhenWorkerNotDefined()
    {
        $this->setExpectedException(RoutingException::class);

        $envelope = new Envelope('test', new SimpleMessage('test', 'body'));
        $router = new SimpleRouter([]);

        $router->map($envelope);
    }
}
