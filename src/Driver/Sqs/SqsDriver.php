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

class SqsDriver implements Provider, Publisher
{
    private $client;
    private $serializer;
    private $logger;
    private $queueName;

    public function __construct(SqsClient $client, Serializer $serializer, LoggerInterface $logger, $queueName)
    {
        $this->client = $client;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->queueName = $queueName;
    }

    public function publish(Message $message)
    {
        $this->logger->debug('Sending message');
        $this->client->sendMessage([
            'QueueUrl' => $this->getQueueUrl($message->getKey()),
            'MessageBody' => $this->serializer->serialize($message),
        ]);
    }

    /**
     * @return Envelope|null
     */
    public function receive()
    {
        $queueUrl = $this->getQueueUrl($this->queueName);

        $this->logger->debug('Receiving messages');
        $result = $this->client->receiveMessage([
            'QueueUrl' => $queueUrl,
            'MaxNumberOfMessages' => 1,
        ]);

        $messages = $result->get('Messages');
        if ($messages !== null) {
            try {
                $message = $this->serializer->unserialize($messages[0]['Body']);

                return new Envelope($message, $messages[0]['ReceiptHandle']);
            } catch (MalformedMessageException $exception) {
                $this->logger->warning('Unable to unserialize the message', [
                    'queue' => $this->getQueueUrl(),
                    'message' => $messages[0]['Body'],
                ]);
            }
        }
    }

    public function ack(Envelope $envelope)
    {
        $this->logger->debug('Acknowledging message');
        $this->client->deleteMessage([
            'QueueUrl' => $this->getQueueUrl(),
            'ReceiptHandle' => $envelope->getReceipt(),
        ]);
    }

    public function nack(Envelope $envelope)
    {
        if (!$envelope->isRequeued()) {
            $this->logger->debug('Nacked message');
            $this->client->deleteMessage([
                'QueueUrl' => $this->getQueueUrl(),
                'ReceiptHandle' => $envelope->getReceipt(),
            ]);
        }
    }

    public function changeVisibility(Envelope $envelope, int $visibilityTimeout)
    {
        $this->client->changeMessageVisibility([
            'QueueUrl' => $this->getQueueUrl(),
            'ReceiptHandle' => $envelope->getReceipt(),
            'VisibilityTimeout' => $visibilityTimeout,
        ]);
    }

    private function getQueueUrl()
    {
        $result = $this->client->getQueueUrl([
            'QueueName' => $this->queueName,
        ]);

        return $result->get('QueueUrl');
    }
}
