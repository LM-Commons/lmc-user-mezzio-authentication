<?php

declare(strict_types=1);

namespace Lmc\User\Authentication;

use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\Storage\StorageInterface;
use Psr\Container\ContainerInterface;

final class AuthenticationServiceFactory
{
    public function __invoke(ContainerInterface $container): AuthenticationService
    {
        return new AuthenticationService(
            $container->get(StorageInterface::class),
            $container->get(AdapterInterface::class)
        );
    }
}
