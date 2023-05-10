<?php

namespace Modulo\Galeria\Middleware;

use Core\Lib\ValidacaoParseRequest;
use Core\Inferface\MiddlewareInterface;
use Core\Lib\ValidacaoCollection;
use Core\Lib\ValidarCampos;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Validator\Constraints as Assert;

class GaleriaSalvarMiddleware implements MiddlewareInterface
{

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        if ($request->getMethod() == 'POST') {
            $regras = ValidacaoCollection::load([
                'titulo'    => [
                    new Assert\NotBlank(null, "O campo é obrigatório", false),
                    new Assert\Length(null, 5, 255, 'utf8', null, null,
                        "O campo deve ter no minimo {{ limit }} cadacteres",
                        "O campo deve ter no máximo {{ limit }} cadacteres"
                    )
                ],
                'descricao' => new Assert\Optional([
                        new Assert\NotBlank(null, "Descrição não informada", true),
                        new Assert\Length(null, null, 255, 'utf8', null, null,
                            "O campo deve ter no máximo {{ limit }} cadacteres"
                        )
                    ]
                ),
                'publicado' => new Assert\Optional([
                    new Assert\NotBlank(null, "Data não informada", true),
                    new Assert\Regex('/\d{4}\-(0?[1-9]|1[012])\-(0?[1-9]|[12][0-9]|3[01])/', 'Formato da data inválida: Y-m-d')
                ])
            ]);
            ValidarCampos::validar($request, $regras, ValidacaoParseRequest::parse($request));
        }

        return $handler->handle($request);

    }
}