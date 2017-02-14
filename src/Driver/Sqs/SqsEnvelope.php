<?php

declare(strict_types=1);

namespace Keystone\Queue\Driver\Sqs;

use Keystone\Queue\Envelope;
use Keystone\Queue\Message;

class SqsEnvelope extends Envelope
{
    /**
     * The result from SQS containing the message attributes.
     *
     * @var array
     */
    private $result;

    /**
     * @param string $queueName
     * @param string $receipt
     * @param Message $message
     * @param array $result
     */
    public function __construct(string $queueName, string $receipt, Message $message, array $result)
    {
        parent::__construct($queueName, $receipt, $message);

        $this->result = $result;
    }

    /**
     * @return int
     */
    public function getAttempts(): int
    {
        return (int) $this->getAttribute('ApproximateReceiveCount');
    }

    /**
     * Returns the approximate first receive timestamp as epoch time in milliseconds.
     *
     * @return int
     */
    public function getFirstReceiveTimestamp(): int
    {
        return (int) $this->getAttribute('ApproximateFirstReceiveTimestamp');
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasAttribute(string $name): bool
    {
        return isset($this->result['Attributes'][$name]);
    }

    /**
     * @param string $name
     * @return string
     */
    public function getAttribute(string $name): string
    {
        if ($this->hasAttribute($name)) {
            return $this->result['Attributes'][$name];
        }
    }
}
