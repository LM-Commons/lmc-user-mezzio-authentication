<?php

declare(strict_types=1);

namespace Lmc\User\Authentication\Adapter;

use Lmc\User\Authentication\ConfigProvider;
use Mezzio\Session\SessionInterface;

abstract class AbstractChainableAdapter implements ChainableAdapterInterface
{
    protected SessionInterface $session;

    public function setSession(SessionInterface $session): void
    {
        $this->session = $session;
    }

    /**
     * Check if this adapter is satisfied or not
     */
    public function isSatisfied(): bool
    {
        /** @var array $storage */
        $storage = $this->session->get(ConfigProvider::LMC_USER_SESSION_STORAGE_NAMESPACE);
        return isset($storage['is_satisfied']) && true === $storage['is_satisfied'];
    }

    /**
     * Set if this adapter is satisfied or not
     */
    public function setSatisfied(bool $bool = true): AbstractChainableAdapter
    {
        $storage['is_satisfied'] = $bool;
        $this->session->set(ConfigProvider::LMC_USER_SESSION_STORAGE_NAMESPACE, $storage);
        return $this;
    }
}
