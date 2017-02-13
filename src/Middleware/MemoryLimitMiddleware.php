<?php

declare(strict_types=1);

namespace Keystone\Queue\Middleware;

use Keystone\Queue\Delegate;
use Keystone\Queue\Envelope;
use Keystone\Queue\Middleware;
use Psr\Log\LoggerInterface;

/**
 * Middleware to limit the memory usage of a consumer. If the memory limit is exceeded then the consumer
 * process will be terminated. This is useful to be able to safely end the process between messages rather
 * than allowing it to hit the real PHP memory limit and crash while processing a message.
 */
class MemoryLimitMiddleware implements Middleware
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * The memory limit in MB.
     *
     * @var int
     */
    private $memoryLimit;

    /**
     * @param LoggerInterface $logger
     * @param int $limit
     */
    public function __construct(LoggerInterface $logger, int $memoryLimit)
    {
        $this->logger = $logger;
        $this->memoryLimit = $memoryLimit;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Envelope $envelope, Delegate $delegate): bool
    {
        $return = $delegate->process($envelope);

        if ($this->isMemoryLimitExceeded()) {
            $this->logger->info(sprintf('Reached memory limit (%d MB)', $this->memoryLimit));

            return false;
        }

        return $return;
    }

    /**
     * Check whether the memory limit has been exceeded.
     *
     * @return bool
     */
    private function isMemoryLimitExceeded(): bool
    {
        // Convert the limit from MB to bytes and compare with current usage (that is returned in bytes)
        return memory_get_usage() >= $this->memoryLimit * 1024 * 1024;
    }
}
