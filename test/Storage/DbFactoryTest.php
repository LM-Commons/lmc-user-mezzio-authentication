<?php

declare(strict_types=1);

namespace LmcTest\User\Authentication\Storage;

use Lmc\User\Authentication\Exception\InvalidConfigException;
use Lmc\User\Authentication\Storage\DbFactory;
use Lmc\User\Repository\AdapterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

#[CoversClass(DbFactory::class)]
final class DbFactoryTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @psalm-suppress UnusedVariable
     */
    public function testCreate(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())->method('has')->willReturn(true);
        $container->expects($this->once())->method('get')->willReturn($this->createStub(AdapterInterface::class));
        $factory = new DbFactory();
        $storage = $factory($container);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @psalm-suppress UnusedVariable
     */
    public function testMissingDbAdapter(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())->method('has')->willReturn(false);
        $this->expectException(InvalidConfigException::class);
        $factory = new DbFactory();
        $storage = $factory($container);
    }
}
