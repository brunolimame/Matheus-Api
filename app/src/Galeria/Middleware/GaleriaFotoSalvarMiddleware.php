<?php

namespace Modulo\Galeria\Middleware;

use Core\Lib\ValidacaoParseRequest;
use Core\BadRequestSerializadoException;
use Core\Inferface\MiddlewareInterface;
use Core\Lib\ValidacaoCollection;
use Core\Lib\ValidarCampos;
use Modulo\Galeria\Entity\GaleriaFotoEntity;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Validator\Constraints as Assert;

class GaleriaFotoSalvarMiddleware implements MiddlewareInterface
{

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {

        if ($request->getMethod() == 'POST') {

            $dadosRequisicao = ValidacaoParseRequest::parse($request);
            if (empty($dadosRequisicao['id']) && empty($dadosRequisicao['foto'])) {
                throw new BadRequestSerializadoException($request, serialize([
                    'foto' => 'A foto deve ser enviada'
                ]));
            }
            $parametrosArquivo = GaleriaFotoEntity::getParametrosParaArquivo();

            $regras = ValidacaoCollection::load([
                'legenda' => new Assert\Optional(
                    new Assert\Length(null, null, 255, 'utf8', null, null, null,
                        "O campo deve ter no máximo {{ limit }} caracteres"
                    )
                ),
                'foto'    => new Assert\Image([
                    "maxSize"          => $parametrosArquivo->getTamanho() . "M",
                    "maxSizeMessage"   => "Arquivo muito Grande. Envie arquivos com até {{ limit }}MB.",
                    "minWidth"         => $parametrosArquivo->getMedidas()[1][0],
                    "minWidthMessage"  => "A imagem é muito pequena ({{ width }}px). Envie imagens com no minimo {{ min_width }}px.",
                    "mimeTypes"        => $parametrosArquivo->getTipo(),
                    "mimeTypesMessage" => "Tipo de imagem inválida.",
                ])
            ]);
            ValidarCampos::validar($request, $regras, $dadosRequisicao);
        }

        return $handler->handle($request);

    }
}