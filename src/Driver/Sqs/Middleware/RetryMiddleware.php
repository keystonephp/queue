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
        $delay = $this->strategy->getDelay($envelope->getAttempts());

        // The maximum visibility timeout for SQS is 12 hours
        // TODO: Adjust the delay based on the current visibility timeout
        $visibilityTimeout = min(static::MAX_VISIBILITY_TIMEOUT, $delay);
        $this->driver->changeVisibility($envelope, $visibilityTimeout);

        // Mark the message as requeued so it is not deleted
        $envelope->requeue();
    }
}
