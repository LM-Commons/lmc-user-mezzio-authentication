<?php

declare(strict_types=1);

namespace Lmc\User\Authentication\Adapter;

use Laminas\Authentication\Storage\StorageInterface;

interface ChainableAdapterInterface
{
    public function authenticate(AdapterChainEvent $event): bool;

    public function getStorage(): StorageInterface;
}
