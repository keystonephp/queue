<?php

declare(strict_types=1);

namespace Keystone\Queue\Middleware\Doctrine;

use Assert\Assertion;
use Doctrine\Common\Persistence\ConnectionRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Keystone\Queue\Delegate;
use Keystone\Queue\Envelope;
use Keystone\Queue\Middleware;

class ConnectionMiddleware implements Middleware
{
    /**
     * @var Connection[]
     */
    private $connections;

    /**
     * @param ConnectionRegistry|Connection[] $connections
     */
    public function __construct($connections)
    {
        if ($connections instanceof ConnectionRegistry) {
            $connections = $connections->getConnections();
        }

        if (!is_array($connections)) {
            $connections = [$connections];
        }

        Assertion::allIsInstanceOf($connections, Connection::class);

        $this->connections = $connections;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Envelope $envelope, Delegate $delegate): bool
    {
        foreach ($this->connections as $connection) {
            if ($connection->isConnected()) {
                try {
                    $connection->query($connection->getDatabasePlatform()->getDummySelectSQL());
                } catch (DBALException $e) {
                    // Close timed out connections so that using them connects again
                    $connection->close();
                }
            }
        }

        return $delegate->process($envelope);
    }
}
