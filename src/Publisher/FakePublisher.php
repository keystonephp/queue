<?php

declare(strict_types=1);

namespace Keystone\Queue\Publisher;

use Keystone\Queue\Message;
use Keystone\Queue\Publisher;

/**
 * A fake publisher that will record published messages.
 */
class FakePublisher implements Publisher
{
    /**
     * @var Message[]
     */
    private $messages = [];

    /**
     * {@inheritdoc}
     */
    public function publish(Message $message)
    {
        $this->messages[] = $message;
    }

    /**
     * @return Message[]
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
