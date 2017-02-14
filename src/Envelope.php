<?php

declare(strict_types=1);

namespace Keystone\Queue;

class Envelope
{
    /**
     * @var string
     */
    private $queueName;

    /**
     * @var string
     */
    private $receipt;

    /**
     * @var Message
     */
    private $message;

    /**
     * @var bool
     */
    private $requeued = false;

    /**
     * @param string $queueName
     * @param string $receipt
     * @param Message $message
     */
    public function __construct(string $queueName, string $receipt, Message $message)
    {
        $this->queueName = $queueName;
        $this->receipt = $receipt;
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getQueueName(): string
    {
        return $this->queueName;
    }

    /**
     * @return string
     */
    public function getReceipt(): string
    {
        return $this->receipt;
    }

    /**
     * @return Message
     */
    public function getMessage(): Message
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function getAttempts(): int
    {
        return 1;
    }

    /**
     * Marks the message as being requeued.
     */
    public function requeue()
    {
        $this->requeued = true;
    }

    /**
     * @return bool
     */
    public function isRequeued(): bool
    {
        return $this->requeued;
    }
}
