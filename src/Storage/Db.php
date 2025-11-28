<?php

declare(strict_types=1);

namespace Lmc\User\Authentication\Storage;

use Laminas\Authentication\Storage\Session;
use Laminas\Authentication\Storage\StorageInterface;
use Lmc\User\Repository\AdapterInterface;
use Lmc\User\Repository\UserInterface;
use Mezzio\Session\SessionInterface;

use function is_int;
use function is_scalar;

class Db implements StorageInterface
{
    protected ?UserInterface $resolvedIdentity = null;

    protected ?SessionInterface $session;

    public function __construct(
        private readonly AdapterInterface $adapter,
        ?SessionInterface $session = null
    ) {
        $this->session = $session;
    }

    public function setSession(SessionInterface $session): void
    {
        $this->session = $session;
    }

    /**
     * @inheritDoc
     */
    public function isEmpty(): bool
    {
        if (! $this->session->has(Session::NAMESPACE_DEFAULT)) {
            return true;
        }
        $identity = $this->session->get(Session::NAMESPACE_DEFAULT, null);
        if ($identity === null) {
            $this->clear();
            return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function read(): UserInterface
    {
        if (null !== $this->resolvedIdentity) {
            return $this->resolvedIdentity;
        }
        $identity = $this->session->get(Session::NAMESPACE_DEFAULT, null);
        if (is_int($identity) || is_scalar($identity)) {
            $identity               = $this->adapter->findById($identity);
            $this->resolvedIdentity = $identity;
        }
        return $this->resolvedIdentity;
    }

    /**
     * @inheritDoc
     */
    public function write($contents)
    {
        $this->resolvedIdentity = null;
        $this->session->set(Session::NAMESPACE_DEFAULT, $contents);
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        $this->resolvedIdentity = null;
        $this->session->unset(Session::NAMESPACE_DEFAULT);
    }
}
