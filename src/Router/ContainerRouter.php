<?php

declare(strict_types=1);

namespace Keystone\Queue\Router;

use Keystone\Queue\Envelope;
use Keystone\Queue\Exception\RoutingException;
use Keystone\Queue\Router;

abstract class ContainerRouter implements Router
{
    /**
     * @var array
     */
    private $workerServiceIds;

    /**
     * @param array $workerServiceIds
     */
    public function __construct(array $workerServiceIds)
    {
        $this->workerServiceIds = $workerServiceIds;
    }

    /**
     * {@inheritdoc}
     */
    public function map(Envelope $envelope)
    {
        $className = get_class($envelope->getMessage());
        if (!array_key_exists($className, $this->workerServiceIds)) {
            throw new RoutingException(sprintf(
                'Unable to find worker for message "%s"',
                $className
            ));
        }

        $serviceId = $this->workerServiceIds[$className];
        if (!$this->has($serviceId)) {
            throw new RoutingException(sprintf(
                'Unable to find worker service "%s" for message "%s"',
                $serviceId,
                $className
            ));
        }

        return $this->get($serviceId);
    }

    /**
     * @param string $serviceId
     *
     * @return bool
     */
    abstract protected function has(string $serviceId): bool;

    /**
     * @param string $serviceId
     *
     * @return object
     */
    abstract protected function get(string $serviceId);
}
