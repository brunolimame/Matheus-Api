<?php

namespace Modulo\Comunicado\Entity;

use Core\Entity\Entity;
use Core\Entity\Value\EntityValueId;
use Core\Entity\Value\EntityValueUuid;
use Core\Entity\Value\EntityValueBolean;
use Core\Entity\Value\EntityValueString;

class ComunicadoEntity extends Entity
{
    const TABLE = 'comunicado';

    /** @var EntityValueId */
    public $id;
    /** @var EntityValueUuid */
    public $uuid;
    /** @var EntityValueString */
    public $user_uuid;
    /** @var EntityValueString */
    public $titulo;
    /** @var EntityValueString */
    public $texto;
    /** @var EntityValueString */
    public $data;
    /** @var EntityValueBolean */
    public $fixo;
    /** @var EntityValueBolean */
    public $urgente;
    /** @var EntityValueString */
    public $setor;
    /** @var EntityValueBolean */
    public $status;
}