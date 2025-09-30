<?php

declare(strict_types=1);

namespace Lmc\User\Authentication\Adapter;

use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Authentication\Exception\ExceptionInterface;
use Laminas\Authentication\Result;
use Laminas\EventManager\Event;
use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\EventManager\SharedEventManagerInterface;
use Lmc\User\Authentication\Exception\AuthenticationEventException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use function gettype;
use function is_array;
use function is_object;
use function sprintf;

class AdapterChain implements AdapterInterface
{
    use EventManagerAwareTrait;

    protected ?AdapterChainEvent $event = null;

    /**
     * @inheritDoc
     * @throws ExceptionInterface
     */
    public function authenticate(): Result
    {
        $event  = $this->getEvent();
        $result = new Result(
            $event->getCode() ?? Result::FAILURE_UNCATEGORIZED,
            $event->getIdentity(),
            $event->getMessages()
        );
        $this->resetAdapters();
        return $result;
    }

    public function prepareForAuthentication(RequestInterface $request): ResponseInterface|bool
    {
        $event = $this->getEvent();
        $event->setRequest($request);

        $event->setName(AdapterChainEvent::AUTHENTICATE_PRE);
        $this->getEventManager()->triggerEvent($event);

        $event->setName(AdapterChainEvent::AUTHENTICATE);
        $result = $this->getEventManager()->triggerEventUntil(
            function (mixed $response) {
                return $response instanceof ResponseInterface;
            },
            $event
        );

        if ($result->stopped()) {
            $lastResult = $result->last();
            if (! $lastResult instanceof ResponseInterface) {
                throw new AuthenticationEventException(
                    sprintf(
                        'Auth event was stopped without a response. Got "%s" instead',
                        is_object($result->last()) ? $lastResult::class : gettype($lastResult)
                    )
                );
            }
            return $lastResult;
        }

        if ($event->getIdentity()) {
            $event->setName(AdapterChainEvent::AUTHENTICATE_SUCCESS);
            $this->getEventManager()->triggerEvent($event);
            return true;
        }

        $event->setName(AdapterChainEvent::AUTHENTICATE_FAIL);
        $this->getEventManager()->triggerEvent($event);

        return false;
    }

    /**
     * @throws ExceptionInterface
     */
    public function resetAdapters(): AdapterChain
    {
        $sharedManager = $this->getEventManager()->getSharedManager();

        if ($sharedManager instanceof SharedEventManagerInterface) {
            $listeners = $sharedManager->getListeners(['authenticate'], AdapterChainEvent::AUTHENTICATE);

            /** @var mixed|array $listener */
            foreach ($listeners as $listener) {
                if (is_array($listener) && $listener[0] instanceof ChainableAdapterInterface) {
                    $listener[0]->getStorage()->clear();
                }
            }
        }
        $event = $this->getEvent();
        $event->setName('reset');
        $this->getEventManager()->triggerEvent($event);
        return $this;
    }

    public function logoutAdapters(): AdapterChain
    {
        //Adapters might need to perform additional cleanup after logout
        $event = $this->getEvent();
        $event->setName('logout');
        $this->getEventManager()->triggerEvent($event);
        return $this;
    }

    public function getEvent(): AdapterChainEvent
    {
        if (null === $this->event) {
            $this->setEvent(new AdapterChainEvent());
            /** @psalm-suppress PossiblyNullReference */
            $this->event->setTarget($this);
        }
        return $this->event;
    }

    public function setEvent(Event $event): AdapterChain
    {
        if (! $event instanceof AdapterChainEvent) {
            $eventParams = $event->getParams();
            $event       = new AdapterChainEvent();
            $event->setParams($eventParams);
            $event->setTarget($this);
        }
        $this->event = $event;
        return $this;
    }
}
