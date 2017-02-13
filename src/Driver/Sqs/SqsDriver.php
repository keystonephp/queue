<?php

declare(strict_types=1);

namespace Keystone\Queue\Driver\Sqs;

use Aws\Sqs\SqsClient;
use InvalidArgumentException;
use Keystone\Queue\Envelope;
use Keystone\Queue\Exception\MalformedMessageException;
use Keystone\Queue\Message;
use Keystone\Queue\Message\PrefetchMessageCache;
use Keystone\Queue\Provider;
use Keystone\Queue\Publisher;
use Keystone\Queue\Serializer;
use Psr\Log\LoggerInterface;

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
     * @var PrefetchMessageCache
     */
    private $cache;

    /**
     * @var array
     */
    private $queueUrls = [];

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
    public function __construct(SqsClient $client, Serializer $serializer, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->serializer = $serializer;
        $this->logger = $logger;

        // Create the cache for prefetched messages
        $this->cache = new PrefetchMessageCache();
    }

    /**
     * {@inheritdoc}
     */
    public function publish(Message $message)
    {
        $this->logger->debug('Sending message');

        $this->client->sendMessage([
            // The message key is the queue name for SQS
            'QueueUrl' => $this->getQueueUrl($message->getKey()),
            'MessageBody' => $this->serializer->serialize($message),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function receive(string $queueName)
    {
        $envelope = $this->cache->pop($queueName);
        if ($envelope !== null) {
            return $envelope;
        }

        $this->logger->debug('Receiving messages');
        $result = $this->client->receiveMessage([
            'QueueUrl' => $this->getQueueUrl($queueName),
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
            $this->cache->push($this->createEnvelope($queueName, $message));
        }

        return $this->cache->pop($queueName);
    }

    /**
     * {@inheritdoc}
     */
    public function ack(Envelope $envelope)
    {
        $this->logger->debug('Acknowledging message');
        $this->client->deleteMessage([
            'QueueUrl' => $this->getQueueUrl($envelope->getQueueName()),
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
                'QueueUrl' => $this->getQueueUrl($envelope->getQueueName()),
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
            'QueueUrl' => $this->getQueueUrl($envelope->getQueueName()),
            'ReceiptHandle' => $envelope->getReceipt(),
            'VisibilityTimeout' => $visibilityTimeout,
        ]);
    }

    /**
     * @param string $queueName
     * @param array $result
     *
     * @return Envelope
     */
    private function createEnvelope(string $queueName, $result): Envelope
    {
        try {
            $message = $this->serializer->unserialize($result['Body']);

            return new Envelope($queueName, $result['ReceiptHandle'], $message);
        } catch (MalformedMessageException $exception) {
            $this->logger->warning('Unable to unserialize the message', [
                'queue' => $queueName,
                'message' => $result['Body'],
            ]);
        }
    }

    /**
     * @param string $queueName
     *
     * @return string
     *
     * @throws InvalidArgumentException When the queue URL cannot be retrieved
     */
    private function getQueueUrl(string $queueName): string
    {
        if (!array_key_exists($queueName, $this->queueUrls)) {
            $result = $this->client->getQueueUrl(['QueueName' => $queueName]);
            if ($result) {
                $queueUrl = $result->get('QueueUrl');
                if (!$queueUrl) {
                    throw new InvalidArgumentException(
                        sprintf('The queue "%s" does not exist', $queueName)
                    );
                }
            }

            $this->queueUrls[$queueName] = $queueUrl;
        }

        return $this->queueUrls[$queueName];
    }
}
