<?php

namespace Modulo\Cliente\Entity;

use Core\Entity\Entity;
use Core\Entity\Value\EntityValueId;
use Core\Entity\Value\EntityValueInteiro;
use Core\Entity\Value\EntityValueLog;
use Core\Entity\Value\EntityValueUuid;
use Core\Entity\Value\EntityValueBolean;
use Core\Entity\Value\EntityValueString;
use Core\Entity\Value\EntityValueDatetime;

class ClienteEntity extends Entity
{
    const TABLE = 'cliente';

    /** @var EntityValueId */
    public $id;
    /** @var EntityValueUuid */
    public $uuid;
    /** @var EntityValueUuid */
    public $usuario_uuid;
    /** @var EntityValueString */
    public $razao_social;
    /** @var EntityValueInteiro */
    public $posts;
    /** @var EntityValueString */
    public $assinatura;
    /** @var EntityValueString */
    public $informacao;
    /** @var EntityValueDatetime */
    public $criado;
    /** @var EntityValueDatetime */
    public $alterado;
    /** @var EntityValueLog */
    public $log;
    /** @var EntityValueBolean */
    public $status;
}