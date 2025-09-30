<?php

declare(strict_types=1);

namespace Lmc\User\Authentication;

use Psr\Http\Message\RequestInterface;

interface RedirectUriProviderInterface
{
    public function getRedirectUri(RequestInterface $request): string;
}
