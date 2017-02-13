<?php

declare(strict_types=1);

namespace Keystone\Queue;

class Envelope
{
    /**
     * @var Message
     */
    private $message;

    /**
     * @var string
     */
    private $receipt;

    /**
     * @var bool
     */
    private $requeued = false;

    /**
     * @param Message $message
     * @param string $receipt
     */
    public function __construct(Message $message, string $receipt = '')
    {
        $this->message = $message;
        $this->receipt = $receipt;
    }

    /**
     * @return Message
     */
    public function getMessage(): Message
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getReceipt()
    {
        return $this->receipt;
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
