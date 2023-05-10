<?php

namespace Core\Lib\Arquivo;

use Core\Lib\Slug;
use Psr\Http\Message\UploadedFileInterface;
use Ramsey\Uuid\Uuid as RamseyUuid;

final class ArquivoNovosDados
{
    public $uuid;
    public $extensao;
    public $tipo;
    public $temp;
    public $nomeOriginal;
    public $nomeOriginalCompleto;
    public $nomeNovo;
    public $slug;
    public $qualidade;

    static public function load(UploadedFileInterface $imagem)
    {
        $dados                       = new static();
        $dados->uuid                 = RamseyUuid::uuid4()->toString();
        $dados->extensao             = pathinfo($imagem->getClientFilename(), PATHINFO_EXTENSION);
        $dados->tipo                 = $imagem->getClientMediaType();
        $dados->temp                 = $imagem->getStream()->getMetadata('uri');
        $dados->nomeOriginal         = str_replace(".{$dados->extensao}", "", $imagem->getClientFilename());
        $dados->nomeOriginalCompleto = $imagem->getClientFilename();
        $dados->slug                 = Slug::slug($dados->nomeOriginal);
        $dados->nomeNovo             = sprintf("%s_%s.%s", $dados->slug, $dados->uuid, $dados->extensao);
        $qualidade                   = Imagem::LISTA_QUALIDADE_TIPO[$imagem->getClientMediaType()];
        $dados->qualidade            = !empty($qualidade) ? $qualidade : 80;
        return $dados;
    }
}