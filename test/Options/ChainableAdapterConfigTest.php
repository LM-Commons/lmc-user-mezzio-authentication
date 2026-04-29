<?php

declare(strict_types=1);

namespace LmcTest\User\Authentication\Options;

use Lmc\User\Authentication\Options\ChainableAdapterConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ChainableAdapterConfig::class)]
final class ChainableAdapterConfigTest extends TestCase
{
    public function testCreateWithNoConfig(): void
    {
        $options = new ChainableAdapterConfig();
        $this->assertEquals('', $options->getName());
        $this->assertEquals(ChainableAdapterConfig::DEFAULT_PRIORITY, $options->getPriority());
        $this->assertEquals([], $options->getOptions());
    }

    public function testGetterAndSetters(): void
    {
        $options = new ChainableAdapterConfig();
        $options->setName('foo');
        $options->setPriority(10);
        $options->setOptions(['foo' => 'bar']);
        $this->assertEquals('foo', $options->getName());
        $this->assertEquals(10, $options->getPriority());
        $this->assertEquals(['foo' => 'bar'], $options->getOptions());
    }
}
