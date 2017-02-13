<?php

declare(strict_types=1);

namespace Keystone\Queue\Middleware;

use Keystone\Queue\Delegate;
use Keystone\Queue\Envelope;
use Keystone\Queue\Middleware;
use Psr\Log\LoggerInterface;

/**
 * Middleware to limit the maximum execution time of a consumer process before terminating.
 */
class MaxExecutionTimeMiddleware implements Middleware, InitializableMiddleware, SleepyMiddleware
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int|float
     */
    private $maxExecutionTime;

    /**
     * @var int
     */
    private $startTime;

    /**
     * @param LoggerInterface $logger
     * @param int|float $maxExecutionTime
     */
    public function __construct(LoggerInterface $logger, $maxExecutionTime = 100)
    {
        $this->logger = $logger;
        $this->maxExecutionTime = $maxExecutionTime;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $this->startTime = microtime(true);
    }

    /**
     * {@inheritdoc}
     */
    public function process(Envelope $envelope, Delegate $delegate): bool
    {
        $return = $delegate->process($envelope);

        if ($this->isTimeExceeded()) {
            return false;
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function sleep(): bool
    {
        if ($this->isTimeExceeded()) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    private function isTimeExceeded(): bool
    {
        $runningTime = microtime(true) - $this->startTime;
        if ($runningTime > $this->maxExecutionTime) {
            $this->logger->info(sprintf('Reached maximum execution time of %d seconds', $this->maxExecutionTime));

            return true;
        }

        return false;
    }
}
