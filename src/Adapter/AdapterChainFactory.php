<?php

declare(strict_types=1);

namespace Lmc\User\Authentication\Adapter;

use Laminas\EventManager\ListenerAggregateInterface;
use Lmc\User\Authentication\Exception\InvalidConfigException;
use Lmc\User\Authentication\Options\Options;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function sprintf;

final class AdapterChainFactory
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AdapterChain
    {
        $adapterChain = new AdapterChain();
        if ($container->has('EventManager')) {
            $adapterChain->setEventManager($container->get('EventManager'));
        }
        /** @var Options $coreOptions */
        $coreOptions = $container->get(Options::class);

        foreach ($coreOptions->getAuthAdapters() as $adapterConfig) {
            if ($container->has($adapterConfig->getName())) {
                /** @var ListenerAggregateInterface $chainableAdapter */
                $chainableAdapter = $container->get($adapterConfig->getName());
                if (! $chainableAdapter instanceof ListenerAggregateInterface) {
                    throw new InvalidConfigException(
                        sprintf(
                            "Adapter '%s' is not an instance of 'ListenerAggregateInterface'",
                            $chainableAdapter::class
                        )
                    );
                }
            } else {
                throw new InvalidConfigException(
                    sprintf("Adapter '%s' not found", $adapterConfig->getName())
                );
            }
            $chainableAdapter->attach($adapterChain->getEventManager(), $adapterConfig->getPriority());
        }
        return $adapterChain;
    }
}
