<?php

declare(strict_types=1);

namespace Keystone\Queue;

interface Router
{
    /**
     * @param Envelope $envelope
     *
     * @return object
     */
    public function map(Envelope $envelope);
}
