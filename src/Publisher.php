<?php

declare(strict_types=1);

namespace Keystone\Queue;

interface Publisher
{
    /**
     * @param Message $message
     */
    public function publish(Message $message);
}
