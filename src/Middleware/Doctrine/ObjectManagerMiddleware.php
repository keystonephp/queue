<?php

declare(strict_types=1);

namespace Keystone\Queue\Middleware\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;
use Keystone\Queue\Delegate;
use Keystone\Queue\Envelope;
use Keystone\Queue\Middleware;

class ObjectManagerMiddleware implements Middleware
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Envelope $envelope, Delegate $delegate): bool
    {
        $return = $delegate->process($envelope);

        foreach ($this->managerRegistry->getManagers() as $managerName => $manager) {
            if (!$manager->isOpen()) {
                $this->managerRegistry->resetManager($managerName);
            } else {
                $manager->clear();
            }
        }

        return $return;
    }
}
