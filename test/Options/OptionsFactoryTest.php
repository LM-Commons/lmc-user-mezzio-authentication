<?php

declare(strict_types=1);

namespace LmcTest\User\Authentication\Options;

use Lmc\User\Authentication\Exception\InvalidConfigException;
use Lmc\User\Authentication\Options\Options;
use Lmc\User\Authentication\Options\OptionsFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

#[CoversClass(OptionsFactory::class)]
final class OptionsFactoryTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCreate(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $config    = [
            'lmc_user' => [],
        ];
        $container->expects($this->once())
            ->method('get')->with('config')->willReturn($config);
        $factory = new OptionsFactory();
        $this->assertInstanceOf(Options::class, $factory($container));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCreateWithEmptyConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $config    = [];
        $container->expects($this->once())->method('get')->with('config')->willReturn($config);
        $this->expectException(InvalidConfigException::class);
        $factory = new OptionsFactory();
        $factory($container);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testCreateWithInvalidConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $config    = [
            'lmc_user' => 'foo',
        ];
        $container->expects($this->once())->method('get')->with('config')->willReturn($config);
        $this->expectException(InvalidConfigException::class);
        $factory = new OptionsFactory();
        $factory($container);
    }
}
