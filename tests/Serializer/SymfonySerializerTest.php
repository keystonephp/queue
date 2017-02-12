<?php

declare(strict_types=1);

namespace Keystone\Queue\Serializer;

use Keystone\Queue\Message;
use Keystone\Queue\Message\PlainMessage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SymfonySerializerTest extends TestCase
{
    public function testSerialize()
    {
        $symfonySerializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
        $serializer = new SymfonySerializer($symfonySerializer, 'json');

        $message = new PlainMessage('test', 'body');
        $serialized = $serializer->serialize($message);
        $unserialized = $serializer->unserialize($serialized);

        $this->assertInstanceOf(Message::class, $unserialized);
        $this->assertEquals($message, $unserialized);
    }
}
