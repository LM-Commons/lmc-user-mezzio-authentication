<?php

declare(strict_types=1);

namespace LmcTest\User\Authentication;

use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Authentication\Storage\StorageInterface;
use Lmc\User\Authentication\AuthenticationServiceFactory;
use Lmc\User\Authentication\Exception\InvalidConfigException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

#[CoversClass(AuthenticationServiceFactory::class)]
final class AuthenticationServiceFactoryTest extends TestCase
{
    /**
     * @psalm-suppress UnusedVariable
     */
    public function testInvoke(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))
            ->method('has')->willReturnMap([
                [AdapterInterface::class, true],
                [StorageInterface::class, true],
            ]);
        $container->expects($this->exactly(2))
            ->method('get')->willReturnMap([
                [AdapterInterface::class, $this->createStub(AdapterInterface::class)],
                [StorageInterface::class, $this->createStub(StorageInterface::class)],
            ]);
        $factory = new AuthenticationServiceFactory();
        $service = $factory($container);
    }

    /**
     * @psalm-suppress UnusedVariable
     */
    public function testInvokeInvalidConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('has')->with(AdapterInterface::class)
            ->willReturn(false);
        $this->expectException(InvalidConfigException::class);
        $factory = new AuthenticationServiceFactory();
        $service = $factory($container);
    }

    /**
     * @psalm-suppress UnusedVariable
     */
    public function testInvokeInvalidConfig2(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))
            ->method('has')->willReturnMap([
                [AdapterInterface::class, true],
                [StorageInterface::class, false],
            ]);
        $this->expectException(InvalidConfigException::class);
        $factory = new AuthenticationServiceFactory();
        $service = $factory($container);
    }
}
