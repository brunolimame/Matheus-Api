<?php

namespace Modulo\Academia\Entity;

use Core\Entity\Entity;
use Core\Entity\Value\EntityValueBolean;
use Core\Entity\Value\EntityValueLog;
use Core\Entity\Value\EntityValueUuid;
use Core\Entity\Value\EntityValueString;

class AulaEntity extends Entity
{
    const TABLE = 'aula';

    /** @var EntityValueUuid */
    public $uuid;
    /** @var EntityValueString */
    public $nome;
    /** @var EntityValueString */
    public $descricao;
    /** @var EntityValueString */
    public $url;
    /** @var EntityValueBolean */
    public $status;
}