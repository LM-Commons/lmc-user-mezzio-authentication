<?php

declare(strict_types=1);

namespace Lmc\User\Authentication;

use Laminas\Authentication\AuthenticationService;
use Lmc\User\Authentication\Exception\InvalidConfigException;
use Mezzio\Authentication\AuthenticationInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;

use function sprintf;

final class AuthenticationFactory
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AuthenticationInterface
    {
        /** @var AuthenticationService|null $auth */
        $auth = $container->has(AuthenticationService::class)
            ? $container->get(AuthenticationService::class)
            : null;

        if (null === $auth) {
            throw new InvalidConfigException(sprintf(
                'A service with interface %s is missing',
                AuthenticationService::class
            ));
        }

        /** @var ResponseFactoryInterface $responseFactory */
        $responseFactory = $container->get(ResponseFactoryInterface::class);

        /** todo Implement RedirectUriProvider */
/*
        $redirectUriProvider = $container->has(RedirectUriProviderInterface::class)
            ? $container->get(RedirectUriProviderInterface::class)
            : null;
        if (null === $redirectUriProvider) {
            throw new InvalidConfigException(sprintf(
                'The %s service is missing',
                RedirectUriProviderInterface::class
            ));
        }
*/
        return new Authentication(
            $auth,
            $responseFactory,
            '/auth/login'
        );
    }
}
