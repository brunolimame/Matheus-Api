<?php

namespace Modulo\Setores\Entity;

use Core\Entity\Entity;
use Core\Entity\Value\EntityValueId;
use Core\Entity\Value\EntityValueUuid;
use Core\Entity\Value\EntityValueBolean;
use Core\Entity\Value\EntityValueString;

class SetoresEntity extends Entity
{
    const TABLE = 'setor';

    /** @var EntityValueId */
    public $id;
    /** @var EntityValueUuid */
    public $uuid;
    /** @var EntityValueString */
    public $nome;
    /** @var EntityValueBolean */
    public $status;
}