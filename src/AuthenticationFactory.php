<?php

declare(strict_types=1);

namespace Lmc\User\Authentication;

use Laminas\Authentication\AuthenticationService;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\Exception\InvalidConfigException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

use function sprintf;

class AuthenticationFactory
{
    public function __invoke(ContainerInterface $container): AuthenticationInterface
    {
        $auth = $container->has(AuthenticationService::class)
            ? $container->get(AuthenticationService::class)
            : null;

        if (null === $auth) {
            throw new InvalidConfigException(sprintf(
                'The %s service is missing',
                AuthenticationService::class
            ));
        }

        $responseFactory = $container->get(ResponseFactoryInterface::class);

        $redirectUriProvider = $container->has(RedirectUriProviderInterface::class)
            ? $container->get(RedirectUriProviderInterface::class)
            : null;

        if (null === $redirectUriProvider) {
            throw new InvalidConfigException(sprintf(
                'The %s service is missing',
                RedirectUriProviderInterface::class
            ));
        }
        /** @var RedirectUriProviderInterface $redirectUriProvider */
        return new Authentication(
            $auth,
            $responseFactory,
            $redirectUriProvider->getRedirectUri()
        );
    }
}
