<?php

namespace Modulo\Condominio\Middleware;

use Core\Lib\ValidacaoParseRequest;
use Core\Inferface\MiddlewareInterface;
use Core\Lib\Acl\AclCheck;
use Core\Lib\ValidacaoCollection;
use Core\Lib\ValidarCampos;
use Modulo\Condominio\Provider\CondominioAclProvider;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpBadRequestException;
use Symfony\Component\Validator\Constraints as Assert;

class CondominioAuthMiddleware implements MiddlewareInterface
{
    /** @var AclCheck */
    public $condominioAclProvider;
    public function __construct(CondominioAclProvider $condominioAclProvider)
    {
        $this->condominioAclProvider = $condominioAclProvider->getAcl();
    }
    
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        if(!$this->condominioAclProvider->isAuth()){
            throw new HttpBadRequestException($request,'Acesso negado');
        }
    
        return $handler->handle($request);

    }
}