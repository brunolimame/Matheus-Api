<?php

namespace Modulo\Registro\Entity;

use Core\Entity\Entity;
use Core\Entity\Value\EntityValueUuid;
use Core\Entity\Value\EntityValueBolean;
use Core\Entity\Value\EntityValueString;
use Core\Entity\Value\EntityValueDatetime;

class RegistroEntity extends Entity
{
    const TABLE = 'registro';

    /** @var EntityValueUuid */
    public $uuid;

    /** @var EntityValueString */
    public $ip;

    /** @var EntityValueString */
    public $mac;

    /** @var EntityValueString */
    public $data;

    /** @var EntityValueString */
    public $tempo;

    /** @var EntityValueBolean */
    public $ativo;

    /** @var EntityValueDatetime */
    public $inicio;

    /** @var EntityValueDatetime */
    public $fim;

    public function noToArray()
    {
      return ['inicio', 'fim'];
    }
}