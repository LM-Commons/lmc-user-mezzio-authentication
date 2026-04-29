<?php

declare(strict_types=1);

namespace LmcTest\User\Authentication\Adapter;

use Lmc\User\Authentication\Adapter\AdapterChainEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

#[CoversClass(AdapterChainEvent::class)]
final class AdapterChainEventTest extends TestCase
{
    public function testEvent(): void
    {
        $event = new AdapterChainEvent();
        $event->setIdentity();
        $this->assertEquals(null, $event->getIdentity());
        $this->assertEquals(null, $event->getCode());
        $this->assertEquals([], $event->getMessages());

        $this->assertEquals(123, $event->setCode(123)->getCode());
        $this->assertEquals(['foo'], $event->setMessages(['foo'])->getMessages());

        $request = $this->createStub(ServerRequestInterface::class);
        $this->assertEquals($request, $event->setRequest($request)->getRequest());
    }
}
