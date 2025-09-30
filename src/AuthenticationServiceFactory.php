<?php

declare(strict_types=1);

namespace Lmc\User\Authentication;

use Laminas\Authentication\AuthenticationService;
use Psr\Container\ContainerInterface;

class AuthenticationServiceFactory
{
    public function __invoke(ContainerInterface $container): AuthenticationService
    {
    }
}
