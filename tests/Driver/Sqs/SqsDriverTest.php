<?php

declare(strict_types=1);

namespace Keystone\Queue\Driver\Sqs;

use Aws\Result;
use Aws\Sqs\SqsClient;
use Keystone\Queue\Message\SimpleMessage;
use Keystone\Queue\Serializer;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Log\NullLogger;

class SqsDriverTest extends MockeryTestCase
{
    private $client;
    private $serializer;
    private $driver;

    public function setUp()
    {
        $this->client = Mockery::mock(SqsClient::class);

        $this->client->shouldReceive('getQueueUrl')
            ->andReturnUsing(function (array $args) {
                return new Result(['QueueUrl' => 'https://aws.com/'.$args['QueueName']]);
            });

        $this->serializer = Mockery::mock(Serializer::class);
        $this->driver = new SqsDriver($this->client, $this->serializer, new NullLogger());
    }

    public function testPublish()
    {
        $message = new SimpleMessage('test', 'body');

        $this->serializer->shouldReceive('serialize')
            ->once()
            ->with($message)
            ->andReturn('body');

        $this->client->shouldReceive('sendMessage')
            ->once()
            ->with([
                'QueueUrl' => 'https://aws.com/test',
                'MessageBody' => 'body',
            ]);

        $this->driver->publish($message);
    }

    public function testAckDeletesTheMessage()
    {
        $envelope = new SqsEnvelope('test', 'receipt', new SimpleMessage('test', 'body'), []);

        $this->client->shouldReceive('deleteMessage')
            ->once()
            ->with([
                'QueueUrl' => 'https://aws.com/test',
                'ReceiptHandle' => 'receipt',
            ]);

        $this->driver->ack($envelope);
    }

    public function testNackDeletesTheMessage()
    {
        $envelope = new SqsEnvelope('test', 'receipt', new SimpleMessage('test', 'body'), []);

        $this->client->shouldReceive('deleteMessage')
            ->once()
            ->with([
                'QueueUrl' => 'https://aws.com/test',
                'ReceiptHandle' => 'receipt',
            ]);

        $this->driver->nack($envelope);
    }

    public function testNackDoesNotDeleteWhenRequeued()
    {
        $envelope = new SqsEnvelope('test', 'receipt', new SimpleMessage('test', 'body'), []);
        $envelope->requeue();

        $this->client->shouldReceive('deleteMessage')
            ->never();

        $this->driver->nack($envelope);
    }

    public function testChangeVisibility()
    {
        $envelope = new SqsEnvelope('test', 'receipt', new SimpleMessage('test', 'body'), []);

        $this->client->shouldReceive('changeMessageVisibility')
            ->once()
            ->with([
                'QueueUrl' => 'https://aws.com/test',
                'ReceiptHandle' => 'receipt',
                'VisibilityTimeout' => 10,
            ]);

        $this->driver->changeVisibility($envelope, 10);
    }

    public function testChangeVisibilityCannotExceedLimit()
    {
        $envelope = new SqsEnvelope('test', 'receipt', new SimpleMessage('test', 'body'), []);

        $this->client->shouldReceive('changeMessageVisibility')
            ->once()
            ->with([
                'QueueUrl' => 'https://aws.com/test',
                'ReceiptHandle' => 'receipt',
                'VisibilityTimeout' => SqsDriver::MAX_VISIBILITY_TIMEOUT,
            ]);

        $this->driver->changeVisibility($envelope, SqsDriver::MAX_VISIBILITY_TIMEOUT + 1);
    }
}
