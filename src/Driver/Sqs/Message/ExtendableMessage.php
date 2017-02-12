<?php

declare(strict_types=1);

namespace Keystone\Queue\Driver\Sqs\Message;

use Keystone\Queue\Message;

interface ExtendableMessage extends Message
{
    public function getVisibilityTimeout();
}
