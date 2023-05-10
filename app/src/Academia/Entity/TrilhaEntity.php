<?php

namespace Modulo\Academia\Entity;

use Core\Entity\Entity;
use Core\Entity\Value\EntityValueBolean;
use Core\Entity\Value\EntityValueId;
use Core\Entity\Value\EntityValueLog;
use Core\Entity\Value\EntityValueUuid;
use Core\Entity\Value\EntityValueString;

class TrilhaEntity extends Entity
{
  const TABLE = 'trilha';

  /** @var EntityValueUuid */
  public $uuid;
  /** @var EntityValueString */
  public $nome;
  /** @var EntityValueLog */
  public $log;
  /** @var EntityValueBolean */
  public $status;
}