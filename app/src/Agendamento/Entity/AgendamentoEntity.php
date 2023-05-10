<?php

namespace Modulo\Agendamento\Entity;

use Core\Entity\Entity;
use Core\Entity\Value\EntityValueId;
use Core\Entity\Value\EntityValueInteiro;
use Core\Entity\Value\EntityValueUuid;
use Core\Entity\Value\EntityValueBolean;
use Core\Entity\Value\EntityValueString;

class AgendamentoEntity extends Entity
{
  const TABLE = 'agendamento';

  /** @var EntityValueId */
  public $id;
  /** @var EntityValueUuid */
  public $uuid;
  /** @var EntityValueUuid */
  public $user_uuid;
  /** @var EntityValueString */
  public $texto;
  /** @var EntityValueInteiro */
  public $ordem;
  /** @var EntityValueBolean */
  public $feito;
  /** @var EntityValueString */
  public $data;
}