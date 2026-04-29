<?php

declare(strict_types=1);

namespace LmcTest\User\Authentication;

use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Authentication\AuthenticationService;
use Lmc\User\Authentication\Authentication;
use Lmc\User\Authentication\Storage\StorageInterface;
use Mezzio\Authentication\UserInterface;
use Mezzio\Session\SessionInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[CoversClass(Authentication::class)]
final class AuthenticationTest extends TestCase
{
    public function testAuthenticate(): void
    {
        $userIdentity = $this->createStub(UserInterface::class);

        $storage = $this->createStub(StorageInterface::class);
        $storage->method('isEmpty')->willReturn(false);
        $storage->method('read')->willReturn($userIdentity);
        $storage->method('setSession');

        $adapter               = $this->createStub(AdapterInterface::class);
        $authenticationService = new AuthenticationService($storage, $adapter);

        $responseFactory = $this->createStub(ResponseFactoryInterface::class);

        $request = $this->createStub(ServerRequestInterface::class);
        $session = $this->createStub(SessionInterface::class);
        $request->method('getAttribute')->willReturn($session);

        $authentication = new Authentication($authenticationService, $responseFactory, '/');
        $this->assertEquals($userIdentity, $authentication->authenticate($request));
    }

    public function testAuthenticateNoIdentity(): void
    {
        $userIdentity = $this->createStub(UserInterface::class);

        $storage = $this->createStub(StorageInterface::class);
        $storage->method('isEmpty')->willReturn(true);
        $storage->method('read')->willReturn($userIdentity);
        $storage->method('setSession');

        $adapter               = $this->createStub(AdapterInterface::class);
        $authenticationService = new AuthenticationService($storage, $adapter);

        $responseFactory = $this->createStub(ResponseFactoryInterface::class);

        $request = $this->createStub(ServerRequestInterface::class);
        $session = $this->createStub(SessionInterface::class);
        $request->method('getAttribute')->willReturn($session);

        $authentication = new Authentication($authenticationService, $responseFactory, '/');
        $this->assertEquals(null, $authentication->authenticate($request));
    }

    public function testUnauthorizedResponse(): void
    {
        $storage               = $this->createStub(StorageInterface::class);
        $adapter               = $this->createStub(AdapterInterface::class);
        $authenticationService = new AuthenticationService($storage, $adapter);

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())->method('withHeader')
            ->with('Location', '/')->willReturnSelf();

        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $responseFactory->expects($this->once())->method('createResponse')
            ->with(301)->willReturn($response);

        $request = $this->createStub(ServerRequestInterface::class);

        $authentication = new Authentication($authenticationService, $responseFactory, '/');
        $this->assertEquals($response, $authentication->unauthorizedResponse($request));
    }
}
