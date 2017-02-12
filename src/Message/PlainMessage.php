<?php

declare(strict_types=1);

namespace Keystone\Queue\Message;

use Keystone\Queue\Message;

class PlainMessage implements Message
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var mixed
     */
    private $body;

    /**
     * @param string $key
     * @param mixed $body
     */
    public function __construct(string $key, $body)
    {
        $this->key = $key;
        $this->body = $body;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }
}
