<?php

namespace Modulo\Tarefa\Entity;

use Core\Entity\Entity;
use Core\Entity\Value\EntityValueId;
use Core\Entity\Value\EntityValueLog;
use Core\Entity\Value\EntityValueUuid;
use Core\Entity\Value\EntityValueString;

class TarefaEntity extends Entity
{
    const TABLE = 'tarefa';

    /** @var EntityValueId */
    public $id;
    /** @var EntityValueUuid */
    public $uuid;
    /** @var EntityValueUuid */
    public $cliente_uuid;
    /** @var EntityValueUuid */
    public $user_uuid;
    /** @var EntityValueString */
    public $post_uuid;
    /** @var EntityValueUuid */
    public $tipo_uuid;
    /** @var EntityValueString */
    public $tema;
    /** @var EntityValueString */
    public $setor;
    /** @var EntityValueString */
    public $data;
    /** @var EntityValueString */
    public $informacao;
    /** @var EntityValueString */
    public $prioridade;
    /** @var EntityValueString */
    public $status;
    /** @var EntityValueLog */
    public $log;
}