<?php

declare(strict_types=1);

namespace Keystone\Queue;

use Keystone\Queue\Message\PlainMessage;
use Keystone\Queue\Middleware\MaxMessagesMiddleware;
use Keystone\Queue\Provider\ArrayProvider;
use Keystone\Queue\Router\ArrayRouter;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class ConsumerTest extends TestCase
{
    public function testConsume()
    {
        $worker = Mockery::mock();

        $message1 = new PlainMessage('test', 1);
        $worker->shouldReceive('process')
            ->once()
            ->with($message1);

        $message2 = new PlainMessage('test', 2);
        $worker->shouldReceive('process')
            ->once()
            ->with($message2);

        $logger = new NullLogger();

        $provider = new ArrayProvider([
            new Envelope('test', $message1),
            new Envelope('test', $message2),
        ]);

        $router = new ArrayRouter([
            PlainMessage::class => $worker,
        ]);

        $consumer = new Consumer($provider, $router, $logger, [new MaxMessagesMiddleware($logger, 2)], 'test', 1);
        $consumer->consume();
    }
}
