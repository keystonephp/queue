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
     * The maximum visibility timeout (12 hours).
     */
    const MAX_VISIBILITY_TIMEOUT = 43200;

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
            'QueueUrl' => $this->resolveQueueUrl($message->getKey()),
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
            'QueueUrl' => $this->resolveQueueUrl($queueName),
            'MaxNumberOfMessages' => $this->prefetch,
            'WaitTimeSeconds' => $this->waitTime,
            'AttributeNames' => ['ApproximateReceiveCount', 'ApproximateFirstReceiveTimestamp'],
        ]);

        if (!$result) {
            return null;
        }

        $messages = $result->get('Messages');
        if ($messages === null) {
            return null;
        }

        foreach ($messages as $message) {
            try {
                $this->cache->push($this->createEnvelope($queueName, $message));
            } catch (MalformedMessageException $exception) {
                // Ignore the malformed message but log the error
                $this->logger->warning('Unable to unserialize the message', [
                    'queue' => $queueName,
                    'message' => $result['Body'],
                ]);
            }
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
            'QueueUrl' => $this->resolveQueueUrl($envelope->getQueueName()),
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
                'QueueUrl' => $this->resolveQueueUrl($envelope->getQueueName()),
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
            'QueueUrl' => $this->resolveQueueUrl($envelope->getQueueName()),
            'ReceiptHandle' => $envelope->getReceipt(),
            'VisibilityTimeout' => min(static::MAX_VISIBILITY_TIMEOUT, $visibilityTimeout),
        ]);
    }

    /**
     * @param string $queueName
     * @param array $result
     *
     * @return SqsEnvelope
     */
    private function createEnvelope(string $queueName, $result): SqsEnvelope
    {
        $message = $this->serializer->unserialize($result['Body']);

        return new SqsEnvelope($queueName, $result['ReceiptHandle'], $message, $result);
    }

    /**
     * @param string $queueName
     *
     * @return string
     */
    private function resolveQueueUrl(string $queueName): string
    {
        if (!array_key_exists($queueName, $this->queueUrls)) {
            $this->queueUrls[$queueName] = $this->getQueueUrl($queueName);
        }

        return $this->queueUrls[$queueName];
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
        $result = $this->client->getQueueUrl(['QueueName' => $queueName]);
        if ($result) {
            $queueUrl = $result->get('QueueUrl');
            if ($queueUrl) {
                return $queueUrl;
            }
        }

        // The queue URL request failed or did not return a result
        throw new InvalidArgumentException(sprintf('The queue "%s" does not exist', $queueName));
    }
}
