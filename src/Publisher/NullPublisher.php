<?php

declare(strict_types=1);

namespace Keystone\Queue\Publisher;

use Keystone\Queue\Message;
use Keystone\Queue\Publisher;

/**
 * A publisher that does not publish the message.
 */
class NullPublisher implements Publisher
{
    /**
     * {@inheritdoc}
     */
    public function publish(Message $message)
    {
    }
}
