<?php

declare(strict_types=1);

namespace Lmc\User\Authentication\Adapter;

use ArrayAccess;
use Laminas\EventManager\Event;
use Psr\Http\Message\RequestInterface;

use function assert;
use function is_array;

/**
 * @extends Event<object|string|null, array|ArrayAccess|object|array<array-key, int|null>>
 */
class AdapterChainEvent extends Event
{
    public const AUTHENTICATE_PRE     = 'authenticate.pre';
    public const AUTHENTICATE         = 'authenticate';
    public const AUTHENTICATE_SUCCESS = 'authenticate.success';
    public const AUTHENTICATE_FAIL    = 'authenticate.fail';
    public const RESET                = 'reset';
    public const LOGOUT               = 'logout';

    public function getIdentity(): mixed
    {
        return $this->getParam('identity');
    }

    public function setIdentity(mixed $identity = null): AdapterChainEvent
    {
        if (null === $identity) {
            $this->setCode();
            $this->setMessages();
        }
        $this->setParam('identity', $identity);
        return $this;
    }

    public function getCode(): int|null
    {
        return $this->getParam('code');
    }

    public function setCode(int|null $code = null): AdapterChainEvent
    {
        $this->setParam('code', $code);
        return $this;
    }

    public function getMessages(): array
    {
        $messages = $this->getParam('messages') ?: [];
        assert(is_array($messages));
        return $messages;
    }

    public function setMessages(array $messages = []): AdapterChainEvent
    {
        $this->setParam('messages', $messages);
        return $this;
    }

    public function getRequest(): RequestInterface|null
    {
        return $this->getParam('request');
    }

    public function setRequest(RequestInterface|null $request = null): AdapterChainEvent
    {
        $this->setParam('request', $request);
        return $this;
    }
}
