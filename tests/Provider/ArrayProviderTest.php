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
            new Envelope('test', new PlainMessage('key', 'body')),
            new Envelope('test', new PlainMessage('key', 'body')),
            new Envelope('test', new PlainMessage('key', 'body')),
        ];

        $provider = new ArrayProvider($envelopes);

        $this->assertSame($envelopes[0], $provider->receive('test'));
        $this->assertSame($envelopes[1], $provider->receive('test'));
        $this->assertSame($envelopes[2], $provider->receive('test'));
        $this->assertNull($provider->receive('test'));
    }
}
