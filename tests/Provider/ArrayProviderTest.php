<?php

declare(strict_types=1);

namespace Keystone\Queue\Provider;

use Keystone\Queue\Envelope;
use Keystone\Queue\Message\PlainMessage;
use PHPUnit\Framework\TestCase;

class ArrayProviderTest extends TestCase
{
    public function testReceive()
    {
        $envelopes = [
            new Envelope(new PlainMessage('key', 'body')),
            new Envelope(new PlainMessage('key', 'body')),
            new Envelope(new PlainMessage('key', 'body')),
        ];

        $provider = new ArrayProvider($envelopes);

        $this->assertSame($envelopes[0], $provider->receive());
        $this->assertSame($envelopes[1], $provider->receive());
        $this->assertSame($envelopes[2], $provider->receive());
        $this->assertNull($provider->receive());
    }
}
