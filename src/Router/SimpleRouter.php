<?php

declare(strict_types=1);

namespace Keystone\Queue\Router;

use Keystone\Queue\Envelope;
use Keystone\Queue\Exception\RoutingException;
use Keystone\Queue\Router;

class SimpleRouter implements Router
{
    /**
     * @var object[]
     */
    private $workers;

    /**
     * @param object[] $workers
     */
    public function __construct(array $workers = [])
    {
        $this->workers = $workers;
    }

    /**
     * @param string $messageName
     * @param object $worker
     */
    public function add(string $messageName, $worker)
    {
        $this->workers[$messageName] = $worker;
    }

    /**
     * {@inheritdoc}
     */
    public function map(Envelope $envelope)
    {
        $className = get_class($envelope->getMessage());
        if (!array_key_exists($className, $this->workers)) {
            throw new RoutingException(sprintf(
                'Unable to find worker for message "%s"',
                $className
            ));
        }

        return $this->workers[$className];
    }
}
