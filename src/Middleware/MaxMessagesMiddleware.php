<?php

declare(strict_types=1);

namespace Keystone\Queue\Middleware;

use Keystone\Queue\Delegate;
use Keystone\Queue\Envelope;
use Keystone\Queue\Middleware;
use Psr\Log\LoggerInterface;

class MaxMessagesMiddleware implements Middleware
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $maxMessages;

    /**
     * @var int
     */
    private $processed = 0;

    /**
     * @param LoggerInterface $logger
     * @param int $maxMessages
     */
    public function __construct(LoggerInterface $logger, int $maxMessages = 100)
    {
        $this->logger = $logger;
        $this->maxMessages = $maxMessages;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Envelope $envelope, Delegate $delegate): bool
    {
        $result = $delegate->process($envelope);

        if (++$this->processed >= $this->maxMessages) {
            $this->logger->info(sprintf('Reached maximum message limit of %d', $this->maxMessages));

            return false;
        }

        return $result;
    }
}
