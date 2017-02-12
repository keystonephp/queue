<?php

declare(strict_types=1);

namespace Keystone\Queue;

use Assert\Assertion;
use SplQueue;

class Delegate
{
    /**
     * @var object
     */
    private $worker;

    /**
     * @var SplQueue
     */
    private $middlewares;

    /**
     * @param object $worker
     * @param Middleware[] $middlewares
     */
    public function __construct($worker, array $middlewares)
    {
        Assertion::allIsInstanceOf($middlewares, Middleware::class);

        $this->worker = $worker;
        $this->middlewares = new SplQueue();
        foreach ($middlewares as $middleware) {
            $this->middlewares->enqueue($middleware);
        }
    }

    /**
     * @param Envelope $envelope
     *
     * @return bool
     */
    public function process(Envelope $envelope): bool
    {
        if ($this->middlewares->isEmpty()) {
            // No more middleware, process with the worker
            $this->worker->process($envelope->getMessage());

            return true;
        }

        $middleware = $this->middlewares->dequeue();

        return $middleware->process($envelope, $this);
    }
}
