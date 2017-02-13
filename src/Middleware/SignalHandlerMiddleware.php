<?php

declare(strict_types=1);

namespace Keystone\Queue\Middleware;

use Keystone\Queue\Delegate;
use Keystone\Queue\Envelope;
use Keystone\Queue\Middleware;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Middleware to handle signals.
 */
class SignalHandlerMiddleware implements Middleware, InitializableMiddleware
{
    /**
     * @var bool
     */
    private static $shouldExit = false;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $signals;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        if (!extension_loaded('pcntl')) {
            throw new RuntimeException('The pcntl extension must be installed to handle signals');
        }

        $this->logger = $logger;
        $this->signals = [SIGTERM, SIGQUIT, SIGINT];
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        foreach ($this->signals as $signal) {
            pcntl_signal($signal, function () {
                static::$shouldExit = true;
            });
        }
    }

    /**
     * {@inheritdoc}
     */
    public function process(Envelope $envelope, Delegate $delegate): bool
    {
        $return = $delegate->process($envelope);

        if ($this->shouldStop()) {
            return false;
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function sleep()
    {
        return !$this->shouldStop();
    }

    /**
     * @return bool
     */
    private function shouldStop(): bool
    {
        pcntl_signal_dispatch();

        foreach ($this->signals as $signal) {
            pcntl_signal($signal, SIG_DFL);
        }

        if (static::$shouldExit) {
            $this->logger->info('Consumer terminated by signal');

            return true;
        }

        return false;
    }
}
