<?php

namespace Core\Inferface;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface MiddlewareInterface
{
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler);
}