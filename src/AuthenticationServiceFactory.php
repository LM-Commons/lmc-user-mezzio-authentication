<?php

declare(strict_types=1);

namespace Lmc\User\Authentication;

use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\Storage\StorageInterface;
use Lmc\User\Authentication\Exception\InvalidConfigException;
use Psr\Container\ContainerInterface;

use function sprintf;

final class AuthenticationServiceFactory
{
    public function __invoke(ContainerInterface $container): AuthenticationService
    {
        if (! $container->has(AdapterInterface::class)) {
            throw new InvalidConfigException(sprintf(
                'A service has not been defined for the %s interface',
                AdapterInterface::class
            ));
        }

        if (! $container->has(StorageInterface::class)) {
            throw new InvalidConfigException(sprintf(
                'A service has not been defined for the %s interface',
                StorageInterface::class
            ));
        }

        return new AuthenticationService(
            $container->get(StorageInterface::class),
            $container->get(AdapterInterface::class)
        );
    }
}
