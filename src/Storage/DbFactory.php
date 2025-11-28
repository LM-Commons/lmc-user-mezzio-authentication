<?php

declare(strict_types=1);

namespace Lmc\User\Authentication\Storage;

use Laminas\Authentication\Storage\StorageInterface;
use Lmc\User\Repository\AdapterInterface;
use Psr\Container\ContainerInterface;

class DbFactory
{
    public function __invoke(ContainerInterface $container): StorageInterface
    {
        return new Db($container->get(AdapterInterface::class));
    }
}
