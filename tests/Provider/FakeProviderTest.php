<?php

declare(strict_types=1);

namespace Keystone\Queue\Provider;

use Keystone\Queue\Envelope;
use Keystone\Queue\Message\SimpleMessage;
use PHPUnit\Framework\TestCase;

class FakeProviderTest extends TestCase
{
    public function testReceive()
    {
        $envelopes = [
            new Envelope('test', new SimpleMessage('key', 'body')),
            new Envelope('test', new SimpleMessage('key', 'body')),
            new Envelope('test', new SimpleMessage('key', 'body')),
        ];

        $provider = new FakeProvider($envelopes);

        $this->assertSame($envelopes[0], $provider->receive('test'));
        $this->assertSame($envelopes[1], $provider->receive('test'));
        $this->assertSame($envelopes[2], $provider->receive('test'));
        $this->assertNull($provider->receive('test'));
    }
}
