<?php

declare(strict_types=1);

namespace Lmc\User\Authentication\Storage;

use Mezzio\Session\SessionInterface;

interface StorageInterface extends \Laminas\Authentication\Storage\StorageInterface
{
    public function setSession(SessionInterface $session): void;
}
