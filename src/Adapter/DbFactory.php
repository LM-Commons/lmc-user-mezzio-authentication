<?php

declare(strict_types=1);

namespace Lmc\User\Authentication\Adapter;

use Lmc\User\Authentication\Options\Options;
use Lmc\User\Repository\UserInterface;
use Psr\Container\ContainerInterface;

class DbFactory
{
    public function __invoke(ContainerInterface $container): Db
    {
        return new Db(
            $container->get(UserInterface::class),
            $container->get(Options::class)
        );
    }
}
