<?php

declare(strict_types=1);

namespace Lmc\User\Authentication\Storage;

use Laminas\Authentication\Storage\Session;
use Lmc\User\Repository\AdapterInterface;
use Lmc\User\Repository\UserInterface;
use Mezzio\Session\SessionInterface;
use Override;

use function is_int;
use function is_string;

final class Db implements StorageInterface
{
    protected ?UserInterface $resolvedIdentity = null;

    protected ?SessionInterface $session;

    public function __construct(
        private readonly AdapterInterface $adapter,
        ?SessionInterface $session = null
    ) {
        $this->session = $session;
    }

    #[Override]
    public function setSession(SessionInterface $session): void
    {
        $this->session = $session;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function isEmpty(): bool
    {
        if (! $this->session->has(Session::NAMESPACE_DEFAULT)) {
            return true;
        }
        /** @var int|string|null $identity */
        $identity = $this->session->get(Session::NAMESPACE_DEFAULT);
        if ($identity === null) {
            $this->clear();
            return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function read(): ?UserInterface
    {
        if (null !== $this->resolvedIdentity) {
            return $this->resolvedIdentity;
        }
        /** @var int|string|null $identity */
        $identity = $this->session->get(Session::NAMESPACE_DEFAULT);
        if (is_int($identity) || is_string($identity)) {
            $identity               = $this->adapter->findById($identity);
            $this->resolvedIdentity = $identity;
        }
        return $this->resolvedIdentity;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function write($contents): void
    {
        $this->resolvedIdentity = null;
        $this->session->set(Session::NAMESPACE_DEFAULT, $contents);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function clear(): void
    {
        $this->resolvedIdentity = null;
        $this->session->unset(Session::NAMESPACE_DEFAULT);
    }
}
