<?php

declare(strict_types=1);

namespace Keystone\Queue;

use Keystone\Queue\Message\SimpleMessage;
use Keystone\Queue\Middleware\MaxMessagesMiddleware;
use Keystone\Queue\Provider\FakeProvider;
use Keystone\Queue\Router\SimpleRouter;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class ConsumerTest extends TestCase
{
    public function testConsume()
    {
        $worker = Mockery::mock();

        $message1 = new SimpleMessage('test', 1);
        $worker->shouldReceive('process')
            ->once()
            ->with($message1);

        $message2 = new SimpleMessage('test', 2);
        $worker->shouldReceive('process')
            ->once()
            ->with($message2);

        $logger = new NullLogger();

        $provider = new FakeProvider([
            new Envelope('test', 'receipt', $message1),
            new Envelope('test', 'receipt', $message2),
        ]);

        $router = new SimpleRouter([
            SimpleMessage::class => $worker,
        ]);

        $consumer = new Consumer($provider, $router, $logger, [new MaxMessagesMiddleware($logger, 2)], 'test', 1);
        $consumer->consume();
    }
}
