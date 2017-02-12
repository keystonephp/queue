<?php

declare(strict_types=1);

namespace Keystone\Queue\Middleware;

use Exception;
use Keystone\Queue\Delegate;
use Keystone\Queue\Envelope;
use Keystone\Queue\Middleware;
use Psr\Log\LoggerInterface;
use Throwable;

class ExceptionCatcherMiddleware implements Middleware
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
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

        return true;
    }

    /**
     * @param Exception|Throwable $exception
     * @param Envelope $envelope
     */
    private function handleException($exception, Envelope $envelope)
    {
        $this->logger->critical('Exception caught when processing message', [
            'exception' => $exception,
        ]);
    }
}
