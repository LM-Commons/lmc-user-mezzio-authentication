<?php

declare(strict_types=1);

namespace Lmc\User\Authentication\Storage;

use Laminas\Authentication\Storage\StorageInterface;
use Lmc\User\Authentication\Exception\InvalidConfigException;
use Lmc\User\Repository\AdapterInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function sprintf;

final class DbFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): StorageInterface
    {
        if (! $container->has(AdapterInterface::class)) {
            throw new InvalidConfigException(sprintf(
                'No service is configured for the "%s" interface.',
                AdapterInterface::class
            ));
        }
        /** @psalm-suppress MixedArgument */
        return new Db($container->get(AdapterInterface::class));
    }
}
