<?php

declare(strict_types=1);

namespace Lmc\User\Authentication\Adapter;

interface ChainableAdapterInterface
{
    public function authenticate(AdapterChainEvent $event): bool;

    public function reset(AdapterChainEvent $event): void;

    public function logout(AdapterChainEvent $event): void;
}
