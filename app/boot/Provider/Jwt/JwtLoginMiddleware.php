<?php

namespace Boot\Provider\Jwt;

use Core\Lib\ValidarCampos;
use Core\Lib\GoogleRecaptcha;
use Core\Lib\ValidacaoCollection;
use Core\Lib\ValidacaoParseRequest;
use Psr\Container\ContainerInterface;
use Core\Inferface\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Validator\Constraints as Assert;

class JwtLoginMiddleware implements MiddlewareInterface
{
//    public $google;
//    public function __construct(ContainerInterface $container)
//    {
//        $this->google = $container->get('_google');
//    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        if ($request->getMethod() == 'POST') {

            $regras = ValidacaoCollection::load([
                '_username'    => [
                    new Assert\NotBlank(null, "Informe o nome de usuário/e-mail", false)
                ],
                '_password'    => [
                    new Assert\NotBlank(null, "Informe sua senha", false)
                ]
            ]);
            ValidarCampos::validar($request, $regras, ValidacaoParseRequest::parse($request));
        }

        return $handler->handle($request);
    }
}
