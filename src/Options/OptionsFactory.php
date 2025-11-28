<?php

declare(strict_types=1);

namespace Lmc\User\Authentication\Options;

use Lmc\User\Authentication\Exception\InvalidConfigException;
use Psr\Container\ContainerInterface;

use function assert;
use function is_array;

class OptionsFactory
{
    public function __invoke(ContainerInterface $container): Options
    {
        $config = $container->get('config');
        assert(is_array($config));

        if (! isset($config['lmc_user']) || ! is_array($config['lmc_user'])) {
            throw new InvalidConfigException("Cannot find a configuration for 'lmc_user'");
        }

        return new Options($config['lmc_user']);
    }
}
