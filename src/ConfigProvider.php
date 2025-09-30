<?php

declare(strict_types=1);

namespace Lmc\User\Authentication;

use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Authentication\Storage\StorageInterface;

class ConfigProvider
{
    public const LMC_USER_SESSION_STORAGE_NAMESPACE = 'LmcUserNamespace';

    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'factories' => [
                Authentication::class   => AuthenticationFactory::class,
                StorageInterface::class => Storage\DbFactory::class,
                Options\Options::class  => Options\OptionsFactory::class,
                Adapter\Db::class       => Adapter\DbFactory::class,
                AdapterInterface::class => Adapter\AdapterChainFactory::class,
            ],
        ];
    }
}
