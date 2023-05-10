<?php

namespace Modulo\Galeria\Middleware;

use Core\Lib\ValidacaoParseRequest;
use Core\Inferface\MiddlewareInterface;
use Core\Lib\Acl\AclCheck;
use Core\Lib\ValidacaoCollection;
use Core\Lib\ValidarCampos;
use Modulo\Galeria\Provider\GaleriaAclProvider;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpBadRequestException;
use Symfony\Component\Validator\Constraints as Assert;

class GaleriaAuthMiddleware implements MiddlewareInterface
{

    /** @var AclCheck */
    public $galeriaAclProvider;
    public function __construct(GaleriaAclProvider $galeriaAclProvider)
    {
        $this->galeriaAclProvider = $galeriaAclProvider->getAcl();
    }
    
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        if(!$this->galeriaAclProvider->isAuth()){
            throw new HttpBadRequestException($request,'Acesso negado');
        }
    
        return $handler->handle($request);

    }
}