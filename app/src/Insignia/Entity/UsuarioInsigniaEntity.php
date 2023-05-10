<?php

namespace Modulo\Insignia\Entity;

use Core\Entity\Entity;
use Core\Entity\Value\EntityValueId;
use Core\Entity\Value\EntityValueUuid;
use Core\Entity\Value\EntityValueString;

class UsuarioInsigniaEntity extends Entity
{
    const TABLE = 'usuario_insignia';

    /** @var EntityValueId */
    public $id;
    /** @var EntityValueUuid */
    public $usuario_uuid;
    /** @var EntityValueUuid */
    public $insignia_uuid;
    /** @var EntityValueString */
    public $titulo;
    /** @var EntityValueString */
    public $informacao;
}