<?php

declare(strict_types=1);

namespace Keystone\Queue;

use Keystone\Queue\Exception\MalformedMessageException;

interface Serializer
{
    /**
     * @param Message $message
     *
     * @return string
     */
    public function serialize(Message $message): string;

    /**
     * @param string $message
     *
     * @return Message
     *
     * @throws MalformedMessageException
     */
    public function unserialize(string $message): Message;
}
