<?php

namespace Modulo\Academia\Entity;

use Core\Entity\Entity;
use Core\Entity\Value\EntityValueBolean;
use Core\Entity\Value\EntityValueDatetime;
use Core\Entity\Value\EntityValueUuid;
use Core\Entity\Value\EntityValueString;

class ForumTopicoEntity extends Entity
{
  const TABLE = 'forum_topico';

  /** @var EntityValueUuid */
  public $uuid;
  /** @var EntityValueString */
  public $categoria_uuid;
  /** @var EntityValueString */
  public $nome;
  /** @var EntityValueString */
  public $texto;
  /** @var EntityValueDatetime */
  public $criado;
  /** @var EntityValueBolean */
  public $status;
  /** @var EntityValueBolean */
  public $excluido;
}