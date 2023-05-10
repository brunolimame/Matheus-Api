<?php

namespace Boot\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class ValidarAcessoAutenticadoMiddleware implements MiddlewareInterface {
    
    /**
     * @var ContainerInterface
     */
    private $container;

     public function __construct(ContainerInterface $container)
     {
         $this->container = $container;
     }

    public function process(Request $request, RequestHandler $handler): ResponseInterface
    {
        
        return $handler->handle($request);
//         return $response;
    }
}