<?php

namespace Modulo\Video\Middleware;

use Core\Lib\ValidacaoParseRequest;
use Core\Inferface\MiddlewareInterface;
use Core\Lib\ValidacaoCollection;
use Core\Lib\ValidarCampos;
use Modulo\Video\Entity\VideoEntity;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Validator\Constraints as Assert;

class VideoSalvarMiddleware implements MiddlewareInterface
{

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        if ($request->getMethod() == 'POST') {
            $dadosRequisicao   = ValidacaoParseRequest::parse($request);
            $parametrosArquivo = VideoEntity::getParametrosParaArquivo();
            $regras            = ValidacaoCollection::load([
                'titulo' => [
                    new Assert\NotBlank(null, "O campo é obrigatório"),
                    new Assert\Length(null, 5, 255, 'utf8', null, null,
                        "O campo deve ter no minimo {{ limit }} cadacteres",
                        "O campo deve ter no máximo {{ limit }} cadacteres"
                    )
                ],
                'local'  => new Assert\Choice([
                    'choices' => array_keys(VideoEntity::LOCAIS_SUPORTADOS),
                    "message" => "O local {{ value }} é inválido. Os locais suportados são: {{ choices }}"
                ]),
                'codigo' => new Assert\NotBlank(null, "O campo é obrigatório", false),
                'foto'   => new Assert\Optional([
                        new Assert\NotBlank(null, "Arquivo não enviado", false),
                        new Assert\Image([
                            "maxSize"          => $parametrosArquivo->getTamanho() . "M",
                            "maxSizeMessage"   => "Arquivo muito Grande. Envie arquivos com até {{ limit }}MB.",
                            "minWidth"         => $parametrosArquivo->getMedidas()[1][0],
                            "minWidthMessage"  => "A imagem é muito pequena ({{ width }}px). Envie imagens com no minimo {{ min_width }}px.",
                            "mimeTypes"        => $parametrosArquivo->getTipo(),
                            "mimeTypesMessage" => "Arquivo inválido.",
                        ])
                    ]
                )
            ]);
            ValidarCampos::validar($request, $regras, $dadosRequisicao);
        }
        return $handler->handle($request);

    }
}