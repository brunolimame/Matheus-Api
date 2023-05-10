<?php

namespace Modulo\Post\Entity;

use Core\Entity\Entity;
use Core\Entity\Value\EntityValueId;
use Core\Entity\Value\EntityValueLog;
use Core\Entity\Value\EntityValueUuid;
use Core\Entity\Value\EntityValueBolean;
use Core\Entity\Value\EntityValueString;
use Core\Entity\Value\EntityValueDatetime;

class PostEntity extends Entity
{
    const TABLE = 'post';

    /** @var EntityValueId */
    public $id;
    /** @var EntityValueUuid */
    public $uuid;
    /** @var EntityValueUuid */
    public $cliente_uuid;
    /** @var EntityValueUuid */
    public $tipo_uuid;
    /** @var EntityValueString */
    public $tema;
    /** @var EntityValueString */
    public $data;
    /** @var EntityValueString */
    public $sugestao;
    /** @var EntityValueString */
    public $texto;
    /** @var EntityValueString */
    public $legenda;
    /** @var EntityValueBolean */
    public $feito;
    /** @var EntityValueDatetime */
    public $criado;
    /** @var EntityValueDatetime */
    public $alterado;
    /** @var EntityValueLog */
    public $log;
    /** @var EntityValueBolean */
    public $status;
}