<?php

declare(strict_types=1);

namespace LmcTest\User\Authentication\Adapter;

use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Lmc\User\Authentication\Adapter\AdapterChainFactory;
use Lmc\User\Authentication\Adapter\Db;
use Lmc\User\Authentication\Exception\InvalidConfigException;
use Lmc\User\Authentication\Options\Options;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use stdClass;

#[CoversClass(AdapterChainFactory::class)]
final class AdapterChainFactoryTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @psalm-suppress UnusedVariable
     */
    public function testCreateAdapterChainNoAdapters(): void
    {
        $options   = new Options([]);
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())->method('has')->willReturn(false);
        $container->expects($this->once())->method('get')->willReturn($options);
        $factory = new AdapterChainFactory();
        $adapter = $factory($container);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCreateAdapterChainSetEventManager(): void
    {
        $options      = new Options([]);
        $eventManager = $this->createStub(EventManagerInterface::class);
        $container    = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())->method('has')->willReturn(true);
        $container->expects($this->exactly(2))->method('get')->willReturnMap([
            ['EventManager', $eventManager],
            [Options::class, $options],
        ]);
        $factory = new AdapterChainFactory();
        $adapter = $factory($container);
        $this->assertEquals($eventManager, $adapter->getEventManager());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @psalm-suppress UnusedVariable
     */
    public function testCreateAdapterChainWithValidAdapters(): void
    {
        $options = new Options([
            'auth_adapters' => [
                10 => [
                    'name' => Db::class,
                ],
            ],
        ]);

        $chainableAdapter = $this->createMock(ListenerAggregateInterface::class);
        $chainableAdapter->expects($this->once())->method('attach');
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->atLeastOnce())->method('has')->willReturnMap([
            ['EventManager', false],
            [Db::class, true],
        ]);
        $container->expects($this->atLeastOnce())->method('get')->willReturnMap([
            [Options::class, $options],
            [Db::class, $chainableAdapter],
        ]);
        $factory = new AdapterChainFactory();
        $adapter = $factory($container);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @psalm-suppress UnusedVariable
     */
    public function testCreateAdapterChainMissingAdapters(): void
    {
        $options = new Options([
            'auth_adapters' => [
                10 => [
                    'name' => Db::class,
                ],
            ],
        ]);

        $chainableAdapter = $this->createMock(ListenerAggregateInterface::class);
        $chainableAdapter->expects($this->never())->method('attach');
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->atLeastOnce())->method('has')->willReturnMap([
            ['EventManager', false],
            [Db::class, false],
        ]);
        $container->expects($this->once())->method('get')->willReturnMap([
            [Options::class, $options],
            [Db::class, $chainableAdapter],
        ]);
        $factory = new AdapterChainFactory();
        $this->expectException(InvalidConfigException::class);
        $adapter = $factory($container);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @psalm-suppress UnusedVariable
     */
    public function testCreateAdapterChainInvalidAdapters(): void
    {
        $options = new Options([
            'auth_adapters' => [
                10 => [
                    'name' => Db::class,
                ],
            ],
        ]);

        $chainableAdapter = new stdClass();
        $container        = $this->createMock(ContainerInterface::class);
        $container->expects($this->atLeastOnce())->method('has')->willReturnMap([
            ['EventManager', false],
            [Db::class, true],
        ]);
        $container->expects($this->atLeastOnce())->method('get')->willReturnMap([
            [Options::class, $options],
            [Db::class, $chainableAdapter],
        ]);
        $factory = new AdapterChainFactory();
        $this->expectException(InvalidConfigException::class);
        $adapter = $factory($container);
    }
}
