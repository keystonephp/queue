<?php

declare(strict_types=1);

namespace Keystone\Queue\Message;

use Keystone\Queue\Envelope;
use SplQueue;

class PrefetchMessageCache
{
    /**
     * @var SplQueue[]
     */
    private $caches = [];

    /**
     * @param Envelope $envelope
     */
    public function push(Envelope $envelope)
    {
        $cache = $this->get($envelope->getQueueName());
        $cache->enqueue($envelope);
    }

    /**
     * @param string $queueName
     *
     * @return Envelope|null
     */
    public function pop(string $queueName)
    {
        $cache = $this->get($queueName);

        if (!$cache->isEmpty()) {
            return $cache->dequeue();
        }
    }

    /**
     * @param string $queueName
     *
     * @return SplQueue
     */
    private function get(string $queueName): SplQueue
    {
        if (!array_key_exists($queueName, $this->caches)) {
            $this->caches[$queueName] = new SplQueue();
        }

        return $this->caches[$queueName];
    }
}
