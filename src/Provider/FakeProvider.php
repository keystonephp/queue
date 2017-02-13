<?php

declare(strict_types=1);

namespace Keystone\Queue\Provider;

use Assert\Assertion;
use Keystone\Queue\Envelope;
use Keystone\Queue\Provider;
use SplQueue;

/**
 * A fake provider that will return the provided message envelopes.
 */
class FakeProvider implements Provider
{
    /**
     * @var Envelope[]
     */
    private $envelopes;

    /**
     * @param Envelope[]
     */
    public function __construct(array $envelopes)
    {
        Assertion::allIsInstanceOf($envelopes, Envelope::class);

        $this->envelopes = new SplQueue();
        foreach ($envelopes as $envelope) {
            $this->envelopes->enqueue($envelope);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function receive(string $queueName)
    {
        if (!$this->envelopes->isEmpty()) {
            return $this->envelopes->dequeue();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function ack(Envelope $envelope)
    {
        // Do nothing
    }

    /**
     * {@inheritdoc}
     */
    public function nack(Envelope $envelope)
    {
        // Do nothing
    }
}
