<?php

declare(strict_types=1);

namespace Lmc\User\Authentication\Adapter;

use Laminas\Authentication\Exception\ExceptionInterface;
use Laminas\Authentication\Storage\Session;
use Laminas\Authentication\Storage\StorageInterface;
use Lmc\User\Authentication\ConfigProvider;
use Mezzio\Session\SessionInterface;

abstract class AbstractChainableAdapter implements ChainableAdapterInterface
{
    protected ?StorageInterface $storage = null;

    protected ?\Mezzio\Session\SessionInterface $session = null;

    protected function setSession(SessionInterface $session): void
    {
        $this->session = $session;
    }

    public function getStorage(): StorageInterface
    {
        if (null === $this->storage) {
            $this->setStorage(new Session(ConfigProvider::LMC_USER_SESSION_STORAGE_NAMESPACE));
        }

        return $this->storage;
    }

    public function setStorage(StorageInterface $storage): self
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * Check if this adapter is satisfied or not
     *
     * @throws ExceptionInterface
     */
    public function isSatisfied(): bool
    {
        $storage = $this->getStorage()->read();
        return isset($storage['is_satisfied']) && true === $storage['is_satisfied'];
    }

    /**
     * Set if this adapter is satisfied or not
     */
    public function setSatisfied(bool $bool = true): AbstractChainableAdapter
    {
        $storage                 = $this->getStorage()->read() ?: [];
        $storage['is_satisfied'] = $bool;
        $this->getStorage()->write($storage);
        return $this;
    }
}
