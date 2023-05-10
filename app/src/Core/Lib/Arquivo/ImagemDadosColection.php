<?php

namespace Core\Lib\Arquivo;

use Core\Entity\Value\EntityValueJson;

class ImagemDadosColection
{
    /** @var ArquivoNovosDados */
    public $dados;
    /** @var EntityValueJson */
    public $arquivos;

    static public function load(ArquivoNovosDados $dados, array $arquivos = [])
    {
        $static           = new static();
        $static->dados    = $dados;
        $static->arquivos = EntityValueJson::factory(json_encode($arquivos));
        return $static;
    }
}