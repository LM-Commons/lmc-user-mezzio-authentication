<?php

declare(strict_types=1);

namespace Lmc\User\Authentication;

interface RedirectUriProviderInterface
{
    public function getRedirectUri(): string;
}
