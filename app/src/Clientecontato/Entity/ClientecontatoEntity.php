<?php

namespace Modulo\Clientecontato\Entity;

use Core\Entity\Entity;
use Core\Entity\Value\EntityValueId;
use Core\Entity\Value\EntityValueInteiro;
use Core\Entity\Value\EntityValueLog;
use Core\Entity\Value\EntityValueUuid;
use Core\Entity\Value\EntityValueBolean;
use Core\Entity\Value\EntityValueString;
use Core\Entity\Value\EntityValueDatetime;

class ClientecontatoEntity extends Entity
{
    const TABLE = 'cliente_contato';

    /** @var EntityValueId */
    public $id;
    /** @var EntityValueUuid */
    public $uuid;
    /** @var EntityValueUuid */
    public $cliente_uuid;
    /** @var EntityValueString */
    public $nome;
    /** @var EntityValueString */
    public $valor;
    /** @var EntityValueLog */
    public $log;
    /** @var EntityValueBolean */
    public $status;
}