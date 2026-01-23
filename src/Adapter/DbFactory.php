<?php

declare(strict_types=1);

namespace Lmc\User\Authentication\Adapter;

use Lmc\User\Authentication\Options\Options;
use Lmc\User\Repository\AdapterInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class DbFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): Db
    {
        /** @psalm-suppress MixedArgument */
        return new Db(
            $container->get(AdapterInterface::class),
            $container->get(Options::class)
        );
    }
}
