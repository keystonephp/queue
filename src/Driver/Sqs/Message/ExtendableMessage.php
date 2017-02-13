<?php

declare(strict_types=1);

namespace Keystone\Queue\Driver\Sqs\Message;

use Keystone\Queue\Message;

/**
 * Used by middleware to determine whether a message should have it's visibility timeout extended.
 */
interface ExtendableMessage extends Message
{
    /**
     * Returns the number of seconds to extend the visibility timeout by.
     *
     * @return int
     */
    public function getVisibilityTimeout(): int;
}
