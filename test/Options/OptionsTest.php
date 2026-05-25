<?php

declare(strict_types=1);

namespace LmcTest\User\Authentication\Options;

use InvalidArgumentException;
use Lmc\User\Authentication\Options\Options;
use Lmc\User\Repository\UserInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Options::class)]
final class OptionsTest extends TestCase
{
    public function testDefaultOptions(): void
    {
        $options = new Options();
        $this->assertFalse($options->getEnableUserState());
        $this->assertEquals(UserInterface::STATE_ACTIVE, $options->getDefaultUserState());
        $this->assertEquals([UserInterface::STATE_ACTIVE], $options->getAllowedLoginStates());
        $this->assertEquals(14, $options->getPasswordCost());
        $this->assertEquals(['email'], $options->getAuthIdentityFields());
        $this->assertEquals([], $options->getAuthAdapters());
    }

    public function testGettersAndSetters(): void
    {
        $options = new Options();
        $options->setEnableUserState(true);
        $options->setDefaultUserState(2);
        $options->setAllowedLoginStates(['foo', 'bar']);
        $options->setPasswordCost(10);
        $options->setAuthIdentityFields(['foo', 'bar']);
        $options->setAuthAdapters([
            20 => [
                'name' => 'foo',
            ],
        ]);
        $this->assertTrue($options->getEnableUserState());
        $this->assertEquals(2, $options->getDefaultUserState());
        $this->assertEquals(['foo', 'bar'], $options->getAllowedLoginStates());
        $this->assertEquals(10, $options->getPasswordCost());
        $this->assertEquals(['foo', 'bar'], $options->getAuthIdentityFields());
        $adapters = $options->getAuthAdapters();
        $adapter  = $adapters[0];
        $this->assertEquals('foo', $adapter->getName());
        $this->assertEquals(20, $adapter->getPriority());
        $this->assertEquals([], $adapter->getOptions());
    }

    public function testAuthAdapters(): void
    {
        $options  = new Options([
            'auth_adapters' => [
                10 => 'foo',
                20 => [
                    'name'    => 'bar',
                    'options' => [
                        'foo' => 'bar',
                    ],
                ],
            ],
        ]);
        $adapters = $options->getAuthAdapters();
        $this->assertCount(2, $adapters);
        $this->assertEquals('foo', $adapters[0]->getName());
        $this->assertEquals(10, $adapters[0]->getPriority());
        $this->assertEquals([], $adapters[0]->getOptions());
        $this->assertEquals('bar', $adapters[1]->getName());
        $this->assertEquals(20, $adapters[1]->getPriority());
        $this->assertEquals(['foo' => 'bar'], $adapters[1]->getOptions());
    }

    public function testAuthAdaptersInvalidConfig(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Options([
            'auth_adapters' => [1, 2],
        ]);
    }

    public function testAuthAdaptersInvalidAdapterConfig(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Options([
            'auth_adapters' => [
                'foo' => [],
            ],
        ]);
    }

    public function testAuthIdentityFieldsInvalidConfigNoName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Options([
            'auth_adapters' => [
                10 => [],
            ],
        ]);
    }

    public function testFindAuthAdaptersByName(): void
    {
        $options = new Options([
            'auth_adapters' => [
                10 => 'foo',
                20 => [
                    'name'    => 'bar',
                    'options' => [
                        'foo' => 'bar',
                    ],
                ],
            ],
        ]);
        $this->assertEquals(null, $options->findAuthAdapterByName('baz'));
        $adapter = $options->findAuthAdapterByName('foo');
        $this->assertNotNull($adapter);
        $this->assertEquals(10, $adapter->getPriority());
    }
}
