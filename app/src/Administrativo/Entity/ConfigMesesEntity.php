<?php

namespace Modulo\Administrativo\Entity;

use Core\Entity\Entity;
use Core\Entity\Value\EntityValueId;
use Core\Entity\Value\EntityValueInteiro;
use Core\Entity\Value\EntityValueLog;
use Core\Entity\Value\EntityValueString;
use Core\Entity\Value\EntityValueDatetime;

class ConfigMesesEntity extends Entity
{
    const TABLE = 'config_meses';

    /** @var EntityValueId */
    public $id;
    /** @var EntityValueString */
    public $data;
    /** @var EntityValueInteiro */
    public $dias;
    /** @var EntityValueDatetime */
    public $criado;
    /** @var EntityValueDatetime */
    public $alterado;
    /** @var EntityValueLog */
    public $log;
}