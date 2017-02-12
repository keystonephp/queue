<?php

declare(strict_types=1);

namespace Keystone\Queue;

interface Message
{
    /**
     * @return string
     */
    public function getKey(): string;
}
