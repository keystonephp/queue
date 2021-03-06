<?php

declare(strict_types=1);

namespace Keystone\Queue\Driver\Sqs\Middleware;

use Exception;
use Keystone\Queue\Delegate;
use Keystone\Queue\Driver\Sqs\SqsDriver;
use Keystone\Queue\Driver\Sqs\SqsEnvelope;
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
     * @param SqsEnvelope $envelope
     */
    private function extendVisibility(SqsEnvelope $envelope)
    {
        // Calculate the retry delay
        $delay = $this->strategy->getDelay($envelope->getAttempts());

        // The maximum visibility timeout is 12 hours for the lifetime of the message
        $maxVisibilityTimeout = SqsDriver::MAX_VISIBILITY_TIMEOUT - (time() - $envelope->getFirstReceiveTimestamp()) - 1;

        // Change the visibility timeout for the message
        $this->driver->changeVisibility($envelope, $maxVisibilityTimeout);

        // Mark the message as requeued so it is not deleted
        $envelope->requeue();
    }
}
