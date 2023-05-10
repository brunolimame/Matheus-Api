<?php

namespace Modulo\Video\Middleware;

use Core\Lib\Acl\AclCheck;
use Core\Inferface\MiddlewareInterface;
use Modulo\Video\Provider\VideoAclProvider;
use Slim\Exception\HttpBadRequestException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class VideoAuthMiddleware implements MiddlewareInterface
{

    /** @var AclCheck */
    public $videoAclProvider;
    
    public function __construct(VideoAclProvider $videoAclProvider)
    {
        $this->videoAclProvider = $videoAclProvider->getAcl();
    }
    
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        if(!$this->videoAclProvider->isAuth()){
            throw new HttpBadRequestException($request,'Acesso negado');
        }
    
        return $handler->handle($request);

    }
}