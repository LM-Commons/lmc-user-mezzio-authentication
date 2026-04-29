<?php

declare(strict_types=1);

namespace LmcTest\User\Authentication\Adapter;

use Laminas\Authentication\Result;
use Laminas\EventManager\EventManagerInterface;
use Lmc\User\Authentication\Adapter\AdapterChainEvent;
use Lmc\User\Authentication\Adapter\Db;
use Lmc\User\Authentication\ConfigProvider;
use Lmc\User\Authentication\Options\Options;
use Lmc\User\Repository\AdapterInterface;
use Lmc\User\Repository\UserInterface;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

use function password_hash;

use const PASSWORD_BCRYPT;

#[CoversClass(Db::class)]
final class DbTest extends TestCase
{
    public function testLogout(): void
    {
        $event   = new AdapterChainEvent();
        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->once())->method('unset');
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->atLeastOnce())->method('getAttribute')->willReturnMap([
            [SessionMiddleware::SESSION_ATTRIBUTE, $session],
        ]);
        $event->setRequest($request);
        $adapter = $this->createStub(AdapterInterface::class);
        $options = new Options();
        $db      = new Db($adapter, $options);
        $db->logout($event);
    }

    public function testCredentialPreprocessor(): void
    {
        $adapter = $this->createStub(AdapterInterface::class);
        $options = new Options();
        $db      = new Db($adapter, $options);
        /** @psalm-suppress UnusedClosureParam */
        $preprocessor = function (string $credential): string {
            return 'bar';
        };
        $this->assertEquals('foo', $db->preProcessCredential('foo'));
        $db->setCredentialPreprocessor($preprocessor);
        $this->assertEquals('bar', $db->preProcessCredential('foo'));
    }

    public function testAttachListeners(): void
    {
        $adapter = $this->createStub(AdapterInterface::class);
        $options = new Options();
        $db      = new Db($adapter, $options);
        $events  = $this->createMock(EventManagerInterface::class);
        $events->expects($this->exactly(3))->method('attach');
        $db->attach($events);
    }

    public function testAuthenticateIsSatisfied(): void
    {
        $adapter = $this->createStub(AdapterInterface::class);
        $options = new Options();
        $db      = new Db($adapter, $options);
        $storage = [
            'is_satisfied' => true,
            'identity'     => 'foo',
        ];
        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->atLeastOnce())->method('get')->willReturnMap([
            [ConfigProvider::LMC_USER_SESSION_STORAGE_NAMESPACE, $storage],
        ]);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())->method('getAttribute')
            ->with(SessionMiddleware::SESSION_ATTRIBUTE)->willReturn($session);
        $event = new AdapterChainEvent();
        $event->setRequest($request);
        $this->assertTrue($db->authenticate($event));
        $this->assertEquals('foo', $event->getIdentity());
        $this->assertEquals(Result::SUCCESS, $event->getCode());
        $this->assertEquals(['Authentication successful.'], $event->getMessages());
    }

    public function testAuthenticateNoCredentialInRequest(): void
    {
        $adapter = $this->createStub(AdapterInterface::class);
        $options = new Options();
        $db      = new Db($adapter, $options);
        $storage = [];
        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->atLeastOnce())->method('get')->willReturnMap([
            [ConfigProvider::LMC_USER_SESSION_STORAGE_NAMESPACE, $storage],
        ]);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())->method('getAttribute')
            ->with(SessionMiddleware::SESSION_ATTRIBUTE)->willReturn($session);
        $request->expects($this->once())->method('getParsedBody')->willReturn([]);
        $event = new AdapterChainEvent();
        $event->setRequest($request);
        $this->assertFalse($db->authenticate($event));
        $this->assertEquals(Result::FAILURE_IDENTITY_NOT_FOUND, $event->getCode());
        $this->assertEquals(['Invalid username or password'], $event->getMessages());
    }

    public function testAuthenticateCredentialNotFoundInRepository(): void
    {
        $adapter = $this->createStub(AdapterInterface::class);
        $adapter->method('findByEmail')->willReturn(null);
        $adapter->method('findByUsername')->willReturn(null);
        $options = new Options([
            'auth_identity_fields' => ['email', 'username'],
        ]);
        $db      = new Db($adapter, $options);
        $storage = [];
        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->atLeastOnce())->method('get')->willReturnMap([
            [ConfigProvider::LMC_USER_SESSION_STORAGE_NAMESPACE, $storage],
        ]);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())->method('getAttribute')
            ->with(SessionMiddleware::SESSION_ATTRIBUTE)->willReturn($session);
        $request->expects($this->once())->method('getParsedBody')
            ->willReturn([
                'identity'   => 'foo',
                'credential' => 'bar',
            ]);
        $event = new AdapterChainEvent();
        $event->setRequest($request);
        $this->assertFalse($db->authenticate($event));
        $this->assertEquals(Result::FAILURE_IDENTITY_NOT_FOUND, $event->getCode());
        $this->assertEquals(['Invalid username or password'], $event->getMessages());
    }

    public function testAuthenticateNoLoginAllowed(): void
    {
        $user = $this->createStub(UserInterface::class);
        $user->method('getState')->willReturn(999);
        $adapter = $this->createStub(AdapterInterface::class);
        $adapter->method('findByEmail')->willReturn($user);
        $options = new Options([
            'enable_user_state'    => true,
            'auth_identity_fields' => ['email'],
        ]);
        $db      = new Db($adapter, $options);
        $storage = [];
        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->atLeastOnce())->method('get')->willReturnMap([
            [ConfigProvider::LMC_USER_SESSION_STORAGE_NAMESPACE, $storage],
        ]);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())->method('getAttribute')
            ->with(SessionMiddleware::SESSION_ATTRIBUTE)->willReturn($session);
        $request->expects($this->once())->method('getParsedBody')
            ->willReturn([
                'identity'   => 'foo',
                'credential' => 'bar',
            ]);
        $event = new AdapterChainEvent();
        $event->setRequest($request);
        $this->assertFalse($db->authenticate($event));
        $this->assertEquals(Result::FAILURE_UNCATEGORIZED, $event->getCode());
        $this->assertEquals(['The user is not allowed to login'], $event->getMessages());
    }

    public function testAuthenticateCredentialNotValid(): void
    {
        $user = $this->createStub(UserInterface::class);
        $user->method('getPassword')->willReturn('foo');
        $adapter = $this->createStub(AdapterInterface::class);
        $adapter->method('findByEmail')->willReturn($user);
        $options = new Options([]);
        $db      = new Db($adapter, $options);
        $storage = [];
        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->atLeastOnce())->method('get')->willReturnMap([
            [ConfigProvider::LMC_USER_SESSION_STORAGE_NAMESPACE, $storage],
        ]);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())->method('getAttribute')
            ->with(SessionMiddleware::SESSION_ATTRIBUTE)->willReturn($session);
        $request->expects($this->once())->method('getParsedBody')
            ->willReturn([
                'identity'   => 'foo',
                'credential' => 'bar',
            ]);
        $event = new AdapterChainEvent();
        $event->setRequest($request);
        $this->assertFalse($db->authenticate($event));
        $this->assertEquals(Result::FAILURE_CREDENTIAL_INVALID, $event->getCode());
        $this->assertEquals(['Invalid username or password'], $event->getMessages());
    }

    public function testAuthenticateValidCredential(): void
    {
        $user = $this->createMock(UserInterface::class);
        $hash = password_hash('bar', PASSWORD_BCRYPT);
        $user->expects($this->exactly(2))->method('getPassword')->willReturn($hash);
        $user->expects($this->once())->method('getIdentity')->willReturn('1');
        $user->expects($this->once())->method('setPassword');

        $adapter = $this->createStub(AdapterInterface::class);
        $adapter->method('findByEmail')->willReturn($user);
        $options = new Options([]);
        $db      = new Db($adapter, $options);
        $storage = [];
        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->atLeastOnce())->method('get')->willReturnMap([
            [ConfigProvider::LMC_USER_SESSION_STORAGE_NAMESPACE, $storage],
        ]);
        $session->expects($this->once())->method('regenerate');
        $session->expects($this->atLeastOnce())->method('set');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->atLeastOnce())->method('getAttribute')->willReturnMap([
            [SessionMiddleware::SESSION_ATTRIBUTE, $session],
            [SessionMiddleware::class, $session],
        ]);
        $request->expects($this->once())->method('getParsedBody')
            ->willReturn([
                'identity'   => 'foo',
                'credential' => 'bar',
            ]);
        $event = new AdapterChainEvent();
        $event->setRequest($request);
        $this->assertTrue($db->authenticate($event));
        $this->assertEquals(Result::SUCCESS, $event->getCode());
        $this->assertEquals(['Authentication successful.'], $event->getMessages());
        $this->assertEquals('1', $event->getIdentity());
    }

    public function testAuthenticateValidCredentialNoHashUpdate(): void
    {
        $user = $this->createMock(UserInterface::class);
        $hash = password_hash('bar', PASSWORD_BCRYPT, ['cost' => 14]);
        $user->expects($this->exactly(2))->method('getPassword')->willReturn($hash);
        $user->expects($this->once())->method('getIdentity')->willReturn('1');
        $user->expects($this->never())->method('setPassword');

        $adapter = $this->createStub(AdapterInterface::class);
        $adapter->method('findByEmail')->willReturn($user);
        $options = new Options([]);
        $db      = new Db($adapter, $options);
        $storage = [];
        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->atLeastOnce())->method('get')->willReturnMap([
            [ConfigProvider::LMC_USER_SESSION_STORAGE_NAMESPACE, $storage],
        ]);
        $session->expects($this->once())->method('regenerate');
        $session->expects($this->atLeastOnce())->method('set');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->atLeastOnce())->method('getAttribute')->willReturnMap([
            [SessionMiddleware::SESSION_ATTRIBUTE, $session],
            [SessionMiddleware::class, $session],
        ]);
        $request->expects($this->once())->method('getParsedBody')
            ->willReturn([
                'identity'   => 'foo',
                'credential' => 'bar',
            ]);
        $event = new AdapterChainEvent();
        $event->setRequest($request);
        $this->assertTrue($db->authenticate($event));
        $this->assertEquals(Result::SUCCESS, $event->getCode());
        $this->assertEquals(['Authentication successful.'], $event->getMessages());
        $this->assertEquals('1', $event->getIdentity());
    }
}
