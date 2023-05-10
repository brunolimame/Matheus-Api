<?php

namespace Modulo\Insignia\Entity;

use Core\Entity\Entity;
use Core\Entity\Value\EntityValueBolean;
use Core\Entity\Value\EntityValueId;
use Core\Entity\Value\EntityValueUuid;
use Core\Entity\Value\EntityValueString;
use Core\Lib\ParametrosArquivo;

class InsigniaEntity extends Entity
{
    const TABLE = 'insignia';

    /** @var EntityValueId */
    public $id;
    /** @var EntityValueUuid */
    public $uuid;
    /** @var EntityValueString */
    public $nome;
    /** @var EntityValueString */
    public $descricao;
    /** @var EntityValueString */
    public $link;
    /** @var EntityValueBolean*/
    public $status;

    static public function novaInsignia()
    {
        $insignia = new self();
        $insignia->status->set(1);
        return $insignia;
    }

    static public function getParametrosParaArquivo()
    {
        return ParametrosArquivo::load(
            "/assets/insignia/",
            10
        );
    }
}