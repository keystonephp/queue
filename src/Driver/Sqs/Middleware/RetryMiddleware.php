<?php

declare(strict_types=1);

namespace Keystone\Queue\Driver\Sqs\Middleware;

use Exception;
use Keystone\Queue\Delegate;
use Keystone\Queue\Driver\Sqs\SqsDriver;
use Keystone\Queue\Envelope;
use Keystone\Queue\Message;
use Keystone\Queue\Message\RetryableMessage;
use Keystone\Queue\Middleware;
use Keystone\Queue\Retry\RetryStrategy;
use Throwable;

/**
 * Middleware to requeue the message to be retried again after some time has passed. The amount
 * of time to delay is determined by the retry strategy.
 */
class RetryMiddleware implements Middleware
{
    /**
     * The maximum visibility timeout (12 hours).
     */
    const MAX_VISIBILITY_TIMEOUT = 43200;

    /**
     * @var SqsDriver
     */
    private $driver;

    /**
     * @var RetryStrategy
     */
    private $strategy;

    private $startTime;

    /**
     * @param SqsDriver $driver
     * @param RetryStrategy $strategy
     */
    public function __construct(SqsDriver $driver, RetryStrategy $strategy)
    {
        $this->driver = $driver;
        $this->strategy = $strategy;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Envelope $envelope, Delegate $delegate): bool
    {
        $this->startTime = time();

        try {
            return $delegate->process($envelope);
        } catch (Exception $exception) {
            $this->handleException($exception, $envelope);
        } catch (Throwable $exception) {
            $this->handleException($exception, $envelope);
        }
    }

    /**
     * @param Exception|Throwable $exception
     * @param Envelope $envelope
     */
    private function handleException($exception, Envelope $envelope)
    {
        if ($envelope->getMessage() instanceof RetryableMessage) {
            $this->extendVisibility($envelope);
        }

        throw $exception;
    }

    /**
     * @param Envelope $envelope
     */
    private function extendVisibility(Envelope $envelope)
    {
        // Calculate the retry delay
        $delay = $this->strategy->getDelay($envelope->getAttempts());

        // The maximum visibility timeout is 12 hours for the lifetime of the message
        $maxVisibilityTimeout = (int) self::MAX_VISIBILITY_TIMEOUT - (time() - ($envelope->getFirstReceiveTimestamp() / 1000)) - 1;

        // Change the visibility timeout for the message
        $this->driver->changeVisibility($envelope, min($maxVisibilityTimeout, $delay));

        // Mark the message as requeued so it is not deleted
        $envelope->requeue();
    }
}
