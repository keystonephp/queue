<?php

declare(strict_types=1);

namespace Keystone\Queue;

interface Middleware
{
    /**
     * @param Envelope $envelope
     * @param Delegate $deleate
     *
     * @return bool
     */
    public function process(Envelope $envelope, Delegate $delegate): bool;
}
