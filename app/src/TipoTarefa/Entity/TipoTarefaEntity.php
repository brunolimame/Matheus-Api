<?php

namespace Modulo\TipoTarefa\Entity;

use Core\Entity\Entity;
use Core\Entity\Value\EntityValueId;
use Core\Entity\Value\EntityValueInteiro;
use Core\Entity\Value\EntityValueUuid;
use Core\Entity\Value\EntityValueBolean;
use Core\Entity\Value\EntityValueString;

class TipoTarefaEntity extends Entity
{
    const TABLE = 'tipotarefa';

    /** @var EntityValueId */
    public $id;
    /** @var EntityValueUuid */
    public $uuid;
    /** @var EntityValueString */
    public $codigo;
    /** @var EntityValueString */
    public $nome;
    /** @var EntityValueInteiro */
    public $pontos;
    /** @var EntityValueBolean */
    public $status;
}