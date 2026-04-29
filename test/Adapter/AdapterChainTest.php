<?php

declare(strict_types=1);

namespace LmcTest\User\Authentication\Adapter;

use Laminas\Authentication\Adapter\Exception\ExceptionInterface;
use Laminas\Authentication\Result;
use Laminas\EventManager\Event;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerInterface;
use Lmc\User\Authentication\Adapter\AdapterChain;
use Lmc\User\Authentication\Adapter\AdapterChainEvent;
use Lmc\User\Authentication\Exception\AuthenticationEventException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

#[CoversClass(AdapterChain::class)]
final class AdapterChainTest extends TestCase
{
    protected bool $preAuthenticateCalled      = false;
    protected bool $authenticateCalled         = false;
    protected bool $authenticatedSuccessCalled = false;
    protected bool $authenticatedFailedCalled  = false;

    public function testGetAndSetEvent(): void
    {
        $adapter = new AdapterChain();
        // Should be an empty event
        $event = $adapter->getEvent();
        $this->assertEquals([], $event->getParams());
        $this->assertInstanceOf(AdapterChain::class, $event->getTarget());

        $event = new AdapterChainEvent();
        $event->setCode(10);
        $adapter->setEvent($event);
        $this->assertEquals(10, $adapter->getEvent()->getCode());

        $event = new Event();
        $event->setParams(['foo' => 'bar']);
        $adapter->setEvent($event);
        $newEvent = $adapter->getEvent();
        $this->assertEquals($event->getParams(), $newEvent->getParams());
    }

    /**
     * @throws ExceptionInterface
     */
    public function testAuthenticate(): void
    {
        $event = new AdapterChainEvent();
        $event->setCode(Result::SUCCESS);
        $event->setIdentity('test');
        $event->setMessages(['Success']);

        $adapter = new AdapterChain();
        $adapter->setEvent($event);
        $result = $adapter->authenticate();
        $this->assertEquals(Result::SUCCESS, $result->getCode());
        $this->assertEquals('test', $result->getIdentity());
        $this->assertEquals(['Success'], $result->getMessages());
    }

    public function testAuthenticateNoCode(): void
    {
        $event = new AdapterChainEvent();
        $event->setIdentity('test');
        $event->setMessages(['Success']);

        $adapter = new AdapterChain();
        $adapter->setEvent($event);
        $result = $adapter->authenticate();
        $this->assertEquals(Result::FAILURE_UNCATEGORIZED, $result->getCode());
        $this->assertEquals('test', $result->getIdentity());
        $this->assertEquals(['Success'], $result->getMessages());
    }

    /** @psalm-suppress UnusedVariable  */
    public function testLogoutAdapters(): void
    {
        $eventManager = $this->createMock(EventManagerInterface::class);
        $eventManager->expects($this->once())->method('triggerEvent');
        $event = new AdapterChainEvent();

        $request = $this->createStub(RequestInterface::class);

        $adapter = new AdapterChain();
        $adapter->setEvent($event);
        $adapter->setEventManager($eventManager);
        $result = $adapter->logoutAdapters($request);
        $this->assertEquals('logout', $event->getName());
        $this->assertEquals($request, $event->getRequest());
    }

    /**
     * @throws \Laminas\Authentication\Exception\ExceptionInterface
     * @psalm-suppress UnusedVariable
     */
    public function testResetAdaptersNoSharedEventManager(): void
    {
        $event   = new AdapterChainEvent();
        $request = $this->createStub(RequestInterface::class);

        $adapter = new AdapterChain();
        $adapter->setEvent($event);
        $result = $adapter->resetAdapters($request);
        $this->assertEquals('reset', $event->getName());
        $this->assertEquals($request, $event->getRequest());
    }

    public function testPrepareForAuthentication(): void
    {
        $event        = new AdapterChainEvent();
        $request      = $this->createStub(RequestInterface::class);
        $eventManager = new EventManager();
        $eventManager->attach(AdapterChainEvent::AUTHENTICATE_PRE, [$this, 'preAuthenticateListener']);
        $eventManager->attach(AdapterChainEvent::AUTHENTICATE, [$this, 'authenticateListener']);
        $eventManager->attach(AdapterChainEvent::AUTHENTICATE_SUCCESS, [$this, 'successAuthenticateListener']);

        $adapter = new AdapterChain();
        $adapter->setEvent($event);
        $adapter->setEventManager($eventManager);
        $result = $adapter->prepareForAuthentication($request);
        $this->assertTrue($result);
        $this->assertTrue($this->preAuthenticateCalled);
        $this->assertTrue($this->authenticateCalled);
        $this->assertTrue($this->authenticatedSuccessCalled);
    }

    public function testPrepareForAuthenticationResultIsResponse(): void
    {
        $event        = new AdapterChainEvent();
        $request      = $this->createStub(RequestInterface::class);
        $eventManager = new EventManager();
        $eventManager->attach(AdapterChainEvent::AUTHENTICATE, [$this, 'authenticateListenerWithResponse']);

        $adapter = new AdapterChain();
        $adapter->setEvent($event);
        $adapter->setEventManager($eventManager);
        $result = $adapter->prepareForAuthentication($request);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    /**
     * @psalm-suppress UnusedVariable
     */
    public function testPrepareForAuthenticationResultIsNotResponse(): void
    {
        $event        = new AdapterChainEvent();
        $request      = $this->createStub(RequestInterface::class);
        $eventManager = new EventManager();
        $eventManager->attach(AdapterChainEvent::AUTHENTICATE, [$this, 'authenticateListenerWithNoResponse']);

        $adapter = new AdapterChain();
        $adapter->setEvent($event);
        $adapter->setEventManager($eventManager);
        $this->expectException(AuthenticationEventException::class);
        $result = $adapter->prepareForAuthentication($request);
    }

    public function testPrepareForAuthenticationFailed(): void
    {
        $event        = new AdapterChainEvent();
        $request      = $this->createStub(RequestInterface::class);
        $eventManager = new EventManager();
        $eventManager->attach(AdapterChainEvent::AUTHENTICATE_FAIL, [$this, 'failedAuthenticateListener']);

        $adapter = new AdapterChain();
        $adapter->setEvent($event);
        $adapter->setEventManager($eventManager);
        $result = $adapter->prepareForAuthentication($request);
        $this->assertFalse($result);
        $this->assertTrue($this->authenticatedFailedCalled);
    }

    /**
     * @psalm-suppress UnusedParam
     */
    public function preAuthenticateListener(AdapterChainEvent $event): void
    {
        $this->preAuthenticateCalled = true;
    }

    public function authenticateListener(AdapterChainEvent $event): void
    {
        $this->authenticateCalled = true;
        $event->setIdentity('test');
    }

    /**
     * @psalm-suppress UnusedParam
     */
    public function successAuthenticateListener(AdapterChainEvent $event): void
    {
        $this->authenticatedSuccessCalled = true;
    }

    /**
     * @psalm-suppress UnusedParam
     */
    public function authenticateListenerWithResponse(AdapterChainEvent $event): ResponseInterface
    {
        $this->authenticateCalled = true;
        return $this->createStub(ResponseInterface::class);
    }

    public function authenticateListenerWithNoResponse(AdapterChainEvent $event): string
    {
        $this->authenticateCalled = true;
        $event->stopPropagation(true);
        return 'foo';
    }

    /**
     * @psalm-suppress UnusedParam
     */
    public function failedAuthenticateListener(AdapterChainEvent $event): void
    {
        $this->authenticatedFailedCalled = true;
    }
}
