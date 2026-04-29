<?php

declare(strict_types=1);

namespace LmcTest\User\Authentication;

use Laminas\Authentication\AuthenticationService;
use Lmc\User\Authentication\AuthenticationFactory;
use Lmc\User\Authentication\Exception\InvalidConfigException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;

#[CoversClass(AuthenticationFactory::class)]
final class AuthenticationFactoryTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @psalm-suppress UnusedVariable
     */
    public function testCreateAuthentication(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('has')->with(AuthenticationService::class)
            ->willReturn(true);
        $container->expects($this->exactly(2))
            ->method('get')->willReturnMap([
                [AuthenticationService::class, $this->createStub(AuthenticationService::class)],
                [ResponseFactoryInterface::class, $this->createStub(ResponseFactoryInterface::class)],
            ]);
        $factory        = new AuthenticationFactory();
        $authentication = $factory($container);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @psalm-suppress UnusedVariable
     */
    public function testCreateAuthenticationException(): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')->willReturn(false);
        $this->expectException(InvalidConfigException::class);
        $factory        = new AuthenticationFactory();
        $authentication = $factory($container);
    }
}
