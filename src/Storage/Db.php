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

    protected SessionInterface $session;

    public function __construct(
        private readonly AdapterInterface $adapter,
        private readonly StorageInterface $storage
    ) {
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
        $storage = $this->session->get(Session::NAMESPACE_DEFAULT);
        if (null === $storage) {
            return true;
        }
        if ($this->storage->isEmpty()) {
            return true;
        }
        $identity = $this->storage->read();
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
        $identity = $this->storage->read();
        if (is_int($identity) || is_scalar($identity)) {
            return $this->adapter->findById($identity);
        }
        return $this->resolvedIdentity;
    }

    /**
     * @inheritDoc
     */
    public function write($contents)
    {
        $this->resolvedIdentity = null;
        $this->storage->write($contents);
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        $this->resolvedIdentity = null;
        $this->storage->clear();
    }
}
