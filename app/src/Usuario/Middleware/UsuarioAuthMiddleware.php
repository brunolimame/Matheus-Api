<?php

namespace Modulo\Usuario\Middleware;

use Core\Inferface\MiddlewareInterface;
use Slim\Exception\HttpBadRequestException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Modulo\Usuario\Provider\UsuarioAclProvider;

class UsuarioAuthMiddleware implements MiddlewareInterface
 {

    /** @var AclCheck */
    public $usuarioAclProvider;
    public function __construct(UsuarioAclProvider $usuarioAclProvider)
    {
        $this->usuarioAclProvider = $usuarioAclProvider->getAcl();
    }
    
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        if(!$this->usuarioAclProvider->isAuth()){
            throw new HttpBadRequestException($request,'Acesso negado');
        }
    
        return $handler->handle($request);

    }
}