<?php

declare(strict_types=1);

namespace Keystone\Queue;

interface Provider
{
    /**
     * @param string $queueName
     *
     * @return Envelope|null
     */
    public function receive(string $queueName);

    /**
     * @param Envelope $envelope
     */
    public function ack(Envelope $envelope);

    /**
     * @param Envelope $envelope
     */
    public function nack(Envelope $envelope);
}
