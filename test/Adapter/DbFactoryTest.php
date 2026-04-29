<?php

declare(strict_types=1);

namespace LmcTest\User\Authentication\Adapter;

use Lmc\User\Authentication\Adapter\DbFactory;
use Lmc\User\Authentication\Exception\InvalidConfigException;
use Lmc\User\Authentication\Options\Options;
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
        $container->expects($this->exactly(2))->method('get')->willReturnMap([
            [AdapterInterface::class, $this->createStub(AdapterInterface::class)],
            [Options::class, new Options()],
        ]);
        $factory = new DbFactory();

        $db = $factory($container);
    }

    public function testCreateInvalidConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())->method('has')->willReturn(false);
        $this->expectException(InvalidConfigException::class);
        $factory = new DbFactory();
        $factory($container);
    }
}
