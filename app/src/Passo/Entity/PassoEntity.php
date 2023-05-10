<?php

namespace Modulo\Passo\Entity;

use Core\Entity\Entity;
use Core\Entity\Value\EntityValueId;
use Core\Entity\Value\EntityValueUuid;
use Core\Entity\Value\EntityValueString;
use Core\Entity\Value\EntityValueDatetime;

class PassoEntity extends Entity
{
    const TABLE = 'passo';

    /** @var EntityValueId */
    public $id;
    /** @var EntityValueUuid */
    public $uuid;
    /** @var EntityValueUuid */
    public $tarefa_uuid;
    /** @var EntityValueString */
    public $titulo;
    /** @var EntityValueString */
    public $informacao;
    /** @var EntityValueDatetime */
    public $criado;

}