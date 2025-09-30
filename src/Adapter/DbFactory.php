<?php

declare(strict_types=1);

namespace Lmc\User\Authentication\Adapter;

use Psr\Container\ContainerInterface;

class DbFactory
{
    public function __invoke(ContainerInterface $container): Db
    {
        return new Db();
    }
}
