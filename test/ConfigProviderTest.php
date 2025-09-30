<?php

declare(strict_types=1);

namespace LmcTest\User\Authentication;

use Lmc\User\Authentication\ConfigProvider;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    public function testGetConfig(): void
    {
        $configProvider = new ConfigProvider();
        $this->assertIsArray($configProvider());
        $this->assertArrayHasKey('dependencies', $configProvider());
    }
}
