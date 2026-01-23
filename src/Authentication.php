<?php

declare(strict_types=1);

namespace Lmc\User\Authentication;

use Laminas\Authentication\AuthenticationService;
use Lmc\User\Authentication\Storage\Db;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\UserInterface;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function assert;

final readonly class Authentication implements AuthenticationInterface
{
    public function __construct(
        private AuthenticationService $authenticationService,
        private ResponseFactoryInterface $responseFactory,
        private string $redirectUri,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function authenticate(ServerRequestInterface $request): ?UserInterface
    {
        /** @var SessionInterface $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        /** @var  Db $storage*/
        $storage = $this->authenticationService->getStorage();
        $storage->setSession($session);

        if (! $this->authenticationService->hasIdentity()) {
            return null;
        }
        $identity = $this->authenticationService->getIdentity();
        assert($identity instanceof UserInterface);
        return $identity;
    }

    /**
     * @inheritDoc
     */
    public function unauthorizedResponse(ServerRequestInterface $request): ResponseInterface
    {
        return $this->responseFactory
            ->createResponse(301)
            ->withHeader(
                'Location',
                $this->redirectUri
            );
    }
}
