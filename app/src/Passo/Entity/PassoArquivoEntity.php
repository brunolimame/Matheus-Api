<?php

namespace Modulo\Passo\Entity;

use Core\Entity\Entity;
use Core\Entity\Value\EntityValueId;
use Core\Entity\Value\EntityValueUuid;
use Core\Entity\Value\EntityValueString;
use Core\Entity\Value\EntityValueDatetime;
use Core\Lib\ParametrosArquivo;

class PassoArquivoEntity extends Entity
{
    const TABLE = 'passo_arquivo';

    /** @var EntityValueId */
    public $id;
    /** @var EntityValueUuid */
    public $uuid;
    /** @var EntityValueUuid */
    public $passo_uuid;
    /** @var EntityValueString */
    public $nome;
    /** @var EntityValueString */
    public $link;
    /** @var EntityValueString */
    public $ext;

    static public function getParametrosParaArquivo()
    {
        return ParametrosArquivo::load(
            "/assets/passo/",
            10
        );
    }
}