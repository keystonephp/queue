<?php

declare(strict_types=1);

namespace Keystone\Queue\Provider;

use Assert\Assertion;
use Keystone\Queue\Envelope;
use Keystone\Queue\Provider;
use SplQueue;

class ArrayProvider implements Provider
{
    private $envelopes;

    public function __construct(array $envelopes)
    {
        Assertion::allIsInstanceOf($envelopes, Envelope::class);

        $this->envelopes = new SplQueue();
        foreach ($envelopes as $envelope) {
            $this->envelopes->enqueue($envelope);
        }
    }

    public function receive()
    {
        if (!$this->envelopes->isEmpty()) {
            return $this->envelopes->dequeue();
        }
    }

    public function ack(Envelope $envelope)
    {
    }

    public function nack(Envelope $envelope)
    {
    }
}
