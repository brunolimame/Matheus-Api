<?php

namespace Modulo\Academia\Entity;

use Core\Entity\Entity;
use Core\Entity\Value\EntityValueBolean;
use Core\Entity\Value\EntityValueUuid;
use Core\Entity\Value\EntityValueString;

class UsuarioAulaEntity extends Entity
{
    const TABLE = 'usuario_aula';

    /** @var EntityValueUuid */
    public $uuid;
    /** @var EntityValueString */
    public $usuario_uuid;
    /** @var EntityValueString */
    public $aula_uuid;
    /** @var EntityValueBolean */
    public $concluido;
}