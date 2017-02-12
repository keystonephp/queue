<?php

declare(strict_types=1);

namespace Keystone\Queue;

interface Provider
{
    /**
     * @return Envelope|null
     */
    public function receive();

    /**
     * @param Envelope $envelope
     */
    public function ack(Envelope $envelope);

    /**
     * @param Envelope $envelope
     */
    public function nack(Envelope $envelope);
}
