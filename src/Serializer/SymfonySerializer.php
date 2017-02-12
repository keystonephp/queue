<?php

declare(strict_types=1);

namespace Keystone\Queue\Serializer;

use Keystone\Queue\Exception\MalformedMessageException;
use Keystone\Queue\Message;
use Keystone\Queue\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class SymfonySerializer implements Serializer
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var string
     */
    private $format;

    /**
     * @var array
     */
    private $context;

    /**
     * @param SerializerInterface $serializer
     * @param string $format
     * @param array $context
     */
    public function __construct(SerializerInterface $serializer, string $format = 'json', array $context = [])
    {
        $this->serializer = $serializer;
        $this->format = $format;
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(Message $message): string
    {
        return get_class($message).':'.$this->serializer->serialize($message, $this->format, $this->context);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize(string $message): Message
    {
        $parts = explode(':', $message, 2);
        if (count($parts) !== 2) {
            throw new MalformedMessageException();
        }

        return $this->serializer->deserialize($parts[1], $parts[0], $this->format, $this->context);
    }
}
