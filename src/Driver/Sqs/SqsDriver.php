<?php

declare(strict_types=1);

namespace Keystone\Queue\Driver\Sqs;

use Aws\Sqs\SqsClient;
use Keystone\Queue\Envelope;
use Keystone\Queue\Exception\MalformedMessageException;
use Keystone\Queue\Message;
use Keystone\Queue\Provider;
use Keystone\Queue\Publisher;
use Keystone\Queue\Serializer;
use Psr\Log\LoggerInterface;
use SplQueue;

class SqsDriver implements Provider, Publisher
{
    /**
     * @var SqsClient
     */
    private $client;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $queueName;

    /**
     * @var string
     */
    private $queueUrl;

    /**
     * @var SplQueue
     */
    private $cache;

    /**
     * @var int
     */
    private $prefetch = 10;

    /**
     * @var int
     */
    private $waitTime = 0;

    /**
     * @param SqsClient $client
     * @param Serializer $serializer
     * @param LoggerInterface $logger
     * @param string $queueName
     */
    public function __construct(SqsClient $client, Serializer $serializer, LoggerInterface $logger, string $queueName)
    {
        $this->client = $client;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->queueName = $queueName;

        // Create the cache for prefetched messages
        $this->cache = new SplQueue();
    }

    /**
     * {@inheritdoc}
     */
    public function publish(Message $message)
    {
        $this->logger->debug('Sending message');

        $this->client->sendMessage([
            'QueueUrl' => $this->getQueueUrl($message->getKey()),
            'MessageBody' => $this->serializer->serialize($message),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function receive()
    {
        if (!$this->cache->isEmpty()) {
            return $this->cache->dequeue();
        }

        $this->logger->debug('Receiving messages');
        $result = $this->client->receiveMessage([
            'QueueUrl' => $this->getQueueUrl(),
            'MaxNumberOfMessages' => $this->prefetch,
            'WaitTimeSeconds' => $this->waitTime,
        ]);

        if (!$result) {
            return;
        }

        $messages = $result->get('Messages');
        if ($messages === null) {
            return;
        }

        foreach ($messages as $message) {
            $this->cache->enqueue($this->createEnvelope($message));
        }

        return $this->cache->dequeue();
    }

    /**
     * {@inheritdoc}
     */
    public function ack(Envelope $envelope)
    {
        $this->logger->debug('Acknowledging message');
        $this->client->deleteMessage([
            'QueueUrl' => $this->getQueueUrl(),
            'ReceiptHandle' => $envelope->getReceipt(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function nack(Envelope $envelope)
    {
        if (!$envelope->isRequeued()) {
            $this->logger->debug('Negatively acknowleged message');
            $this->client->deleteMessage([
                'QueueUrl' => $this->getQueueUrl(),
                'ReceiptHandle' => $envelope->getReceipt(),
            ]);
        } else {
            $this->logger->debug('The message is being requeued');
        }
    }

    /**
     * @param Envelope $envelope
     * @param int $visibilityTimeout
     */
    public function changeVisibility(Envelope $envelope, int $visibilityTimeout)
    {
        $this->client->changeMessageVisibility([
            'QueueUrl' => $this->getQueueUrl(),
            'ReceiptHandle' => $envelope->getReceipt(),
            'VisibilityTimeout' => $visibilityTimeout,
        ]);
    }

    /**
     * @param array $result
     * @return Envelope
     */
    private function createEnvelope($result): Envelope
    {
        try {
            $message = $this->serializer->unserialize($result['Body']);

            return new Envelope($message, $result['ReceiptHandle']);
        } catch (MalformedMessageException $exception) {
            $this->logger->warning('Unable to unserialize the message', [
                'queue' => $this->getQueueUrl(),
                'message' => $result['Body'],
            ]);
        }
    }

    /**
     * @return string
     */
    private function getQueueUrl(): string
    {
        if ($this->queueUrl === null) {
            $result = $this->client->getQueueUrl([
                'QueueName' => $this->queueName,
            ]);

            $this->queueUrl = $result->get('QueueUrl');
        }

        return $this->queueUrl;
    }
}
