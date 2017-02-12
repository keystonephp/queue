<?php

declare(strict_types=1);

namespace Keystone\Queue\Driver\Sqs\Middleware;

use Keystone\Queue\Delegate;
use Keystone\Queue\Driver\Sqs\Message\ExtendableMessage;
use Keystone\Queue\Driver\Sqs\SqsDriver;
use Keystone\Queue\Envelope;
use Keystone\Queue\Message;
use Keystone\Queue\Middleware;

class ExtendVisibilityTimeoutMiddleware implements Middleware
{
    /**
     * @var SqsDriver
     */
    private $driver;

    /**
     * @param SqsDriver $driver
     */
    public function __construct(SqsDriver $driver)
    {
        $this->driver = $driver;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Envelope $envelope, Delegate $delegate): bool
    {
        if ($envelope->getMessage() instanceof ExtendableMessage) {
            $this->driver->changeVisibility($envelope, $envelope->getMessage()->getVisibilityTimeout());
        }

        return $delegate->process($envelope);
    }
}
