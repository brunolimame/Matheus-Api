<?php

namespace Modulo\Versao\Entity;

use Core\Entity\Entity;
use Core\Entity\Value\EntityValueId;
use Core\Entity\Value\EntityValueUuid;
use Core\Entity\Value\EntityValueBolean;
use Core\Entity\Value\EntityValueString;

class VersaoEntity extends Entity
{
    const TABLE = 'versao';

    /** @var EntityValueId */
    public $id;
    /** @var EntityValueString */
    public $versao;
}