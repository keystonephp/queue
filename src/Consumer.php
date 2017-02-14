<?php

declare(strict_types=1);

namespace Keystone\Queue;

use Keystone\Queue\Exception\RoutingException;
use Keystone\Queue\Middleware\InitializableMiddleware;
use Keystone\Queue\Middleware\SleepyMiddleware;
use Keystone\Queue\Middleware\TerminableMiddleware;
use Psr\Log\LoggerInterface;

class Consumer
{
    /**
     * @var Provider
     */
    private $provider;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Middleware[]
     */
    private $middlewares;

    /**
     * @var string
     */
    private $queueName;

    /**
     * The polling interval in seconds.
     *
     * @var int
     */
    private $interval;

    /**
     * @var bool
     */
    private $wait = false;

    /**
     * @param Provider $provider
     * @param Router $router
     * @param LoggerInterface $logger
     * @param Middleware[] $middlewares
     * @param string $queueName
     * @param int $interval
     */
    public function __construct(
        Provider $provider,
        Router $router,
        LoggerInterface $logger,
        array $middlewares,
        string $queueName,
        int $interval
    ) {
        $this->provider = $provider;
        $this->router = $router;
        $this->logger = $logger;
        $this->middlewares = $middlewares;
        $this->queueName = $queueName;
        $this->interval = $interval;
    }

    /**
     * Consume the queue.
     */
    public function consume()
    {
        $this->logger->debug('Starting the consumer');
        $this->initialize();

        while (true) {
            if ($this->process() === false) {
                break;
            }

            if ($this->sleep() === false) {
                break;
            }
        }

        $this->terminate();
        $this->logger->debug('Terminating the consumer');
    }

    /**
     * Initializes the middlewares.
     */
    private function initialize()
    {
        foreach ($this->middlewares as $middleware) {
            if ($middleware instanceof InitializableMiddleware) {
                $middleware->initialize();
            }
        }
    }

    /**
     * Process messages until the queue is empty.
     *
     * @return bool
     */
    private function process(): bool
    {
        while (($envelope = $this->provider->receive($this->queueName)) !== null) {
            try {
                $delegate = new Delegate($this->router->map($envelope), $this->middlewares);
                $result = $delegate->process($envelope);
                if ($result === false) {
                    return false;
                }
            } catch (RoutingException $exception) {
                $this->logger->critical(sprintf('Unable to route message "%s"', get_class($envelope->getMessage())));
                $this->provider->nack($envelope);
            }
        }

        // When the queue is empty then wait before polling again
        $this->wait = ($envelope === null);

        return true;
    }

    /**
     * Sleep inbetween processing message batches.
     *
     * @return bool
     */
    private function sleep(): bool
    {
        foreach ($this->middlewares as $middleware) {
            if ($middleware instanceof SleepyMiddleware && $middleware->sleep() === false) {
                return false;
            }
        }

        if ($this->wait) {
            // Sleep between queue polls when the queue is empty
            usleep($this->interval * 1000000);
        }

        return true;
    }

    /**
     * Terminate the consumer.
     */
    private function terminate()
    {
        foreach ($this->middlewares as $middleware) {
            if ($middleware instanceof TerminableMiddleware) {
                $middleware->terminate();
            }
        }
    }
}
