<?php

declare(strict_types=1);

namespace Keystone\Queue\Middleware;

use Exception;
use Keystone\Queue\Delegate;
use Keystone\Queue\Envelope;
use Keystone\Queue\Middleware;
use Keystone\Queue\Provider;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Middleware to acknowledge messages.
 */
class AckMiddleware implements Middleware
{
    /**
     * @var Provider
     */
    private $provider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Provider $provider
     * @param LoggerInterface $logger
     */
    public function __construct(Provider $provider, LoggerInterface $logger)
    {
        $this->provider = $provider;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Envelope $envelope, Delegate $delegate): bool
    {
        try {
            $return = $delegate->process($envelope);
            $this->provider->ack($envelope);

            $this->logger->info('Message acknowledged');

            return $return;
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
        $this->provider->nack($envelope);

        $this->logger->info('Exception occurred and the message was negatively acknowleged');

        throw $exception;
    }
}
