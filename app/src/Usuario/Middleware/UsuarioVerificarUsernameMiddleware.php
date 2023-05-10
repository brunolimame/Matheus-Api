<?php

namespace Modulo\Usuario\Middleware;

use Core\Lib\ValidarCampos;
use Core\Lib\ValidacaoCollection;
use Core\Inferface\MiddlewareInterface;
use Core\Lib\ValidacaoParseRequest;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Validator\Constraints as Assert;

class UsuarioVerificarUsernameMiddleware implements MiddlewareInterface
{

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        if ($request->getMethod() == 'GET') {
            
            $dadosRequisicao   = ValidacaoParseRequest::parse($request,'GET');
            $regras = ValidacaoCollection::load([
                'username'    => [
                    new Assert\NotBlank(null, "O campo username é obrigatório", false)
                ],
            ]);
            ValidarCampos::validar($request, $regras, $dadosRequisicao);
        }

        return $handler->handle($request);

    }
}