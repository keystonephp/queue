<?php

declare(strict_types=1);

namespace Keystone\Queue\Router;

use Keystone\Queue\Envelope;
use Keystone\Queue\Exception\RoutingException;
use Keystone\Queue\Message\PlainMessage;
use Mockery;
use PHPUnit\Framework\TestCase;

class ArrayRouterTest extends TestCase
{
    public function testMapReturnsWorker()
    {
        $envelope = new Envelope('test', new PlainMessage('test', 'body'));

        $worker = Mockery::mock();
        $router = new ArrayRouter([PlainMessage::class => $worker]);

        $this->assertSame($worker, $router->map($envelope));
    }

    public function testThrowsExceptionWhenWorkerNotDefined()
    {
        $this->setExpectedException(RoutingException::class);

        $envelope = new Envelope('test', new PlainMessage('test', 'body'));
        $router = new ArrayRouter([]);

        $router->map($envelope);
    }
}
