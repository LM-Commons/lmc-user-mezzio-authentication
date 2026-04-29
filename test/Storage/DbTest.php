<?php

declare(strict_types=1);

namespace LmcTest\User\Authentication\Storage;

use Laminas\Authentication\Exception\ExceptionInterface;
use Laminas\Authentication\Storage\Session;
use Lmc\User\Authentication\Storage\Db;
use Lmc\User\Repository\AdapterInterface;
use Lmc\User\Repository\UserInterface;
use Mezzio\Session\SessionInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Db::class)]
final class DbTest extends TestCase
{
    public function testIsEmptyNoSession(): void
    {
        $db      = new Db(
            $this->createStub(AdapterInterface::class),
            null,
        );
        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->once())->method('has')->willReturn(false);
        $db->setSession($session);
        $this->assertTrue($db->isEmpty());
    }

    public function testIsEmptyNoIdentity(): void
    {
        $db      = new Db(
            $this->createStub(AdapterInterface::class),
            null,
        );
        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->once())->method('has')->willReturn(true);
        $session->expects($this->once())->method('get')->willReturn(null);
        $session->expects($this->once())->method('unset');
        $db->setSession($session);
        $this->assertTrue($db->isEmpty());
    }

    public function testIsEmptyWithIdentity(): void
    {
        $db      = new Db(
            $this->createStub(AdapterInterface::class),
            null,
        );
        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->once())->method('has')->willReturn(true);
        $session->expects($this->once())->method('get')->willReturn('foo');
        $db->setSession($session);
        $this->assertFalse($db->isEmpty());
    }

    /**
     * @throws ExceptionInterface
     */
    public function testReadWithIdentity(): void
    {
        $session    = $this->createMock(SessionInterface::class);
        $identityId = 'foo';
        $identity   = $this->createStub(UserInterface::class);
        $session->expects($this->once())->method('get')->willReturn($identityId);
        $adapter = $this->createMock(AdapterInterface::class);
        $adapter->expects($this->once())->method('findById')->willReturn($identity);
        $db = new Db($adapter, $session);
        $this->assertEquals($identity, $db->read());
        // Do it again, session and adapter are not used
        $this->assertEquals($identity, $db->read());
    }

    public function testWrite(): void
    {
        $session  = $this->createMock(SessionInterface::class);
        $contents = 'foo';
        $session->expects($this->once())->method('set')->with(Session::NAMESPACE_DEFAULT, $contents);
        $db = new Db(
            $this->createStub(AdapterInterface::class),
            $session
        );
        $db->write($contents);
    }
}
