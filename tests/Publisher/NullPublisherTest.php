<?php

declare(strict_types=1);

namespace Keystone\Queue\Publisher;

use Keystone\Queue\Message\SimpleMessage;
use PHPUnit\Framework\TestCase;

class NullPublisherTest extends TestCase
{
    public function testCanPublish()
    {
        $publisher = new NullPublisher();

        // Ensure we can publish without errors
        $publisher->publish(new SimpleMessage('key', 'body'));
    }
}
