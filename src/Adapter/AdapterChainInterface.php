<?php

declare(strict_types=1);

namespace Lmc\User\Authentication\Adapter;

use Laminas\Authentication\Result;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface AdapterChainInterface
{
    public function resetAdapters(RequestInterface $request): self;

    public function logoutAdapters(RequestInterface $request): self;

    public function authenticate(): Result;

    public function prepareForAuthentication(RequestInterface $request): bool|ResponseInterface;
}
