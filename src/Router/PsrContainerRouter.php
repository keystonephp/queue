<?php

declare(strict_types=1);

namespace Keystone\Queue\Router;

use Psr\Container\ContainerInterface;

class PsrContainerRouter extends ContainerRouter
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param array $workerServiceIds
     * @param ContainerInterface $container
     */
    public function __construct(array $workerServiceIds, ContainerInterface $container)
    {
        parent::__construct($workerServiceIds);

        $this->container = $container;
    }

    /**
     * @param string $serviceId
     *
     * @return bool
     */
    protected function has(string $serviceId): bool
    {
        return $this->container->has($serviceId);
    }

    /**
     * @param string $serviceId
     *
     * @return object
     */
    protected function get(string $serviceId)
    {
        return $this->container->get($serviceId);
    }
}
