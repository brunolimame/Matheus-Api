<?php

namespace Modulo\Academia\Entity;

use Core\Entity\Entity;
use Core\Entity\Value\EntityValueBolean;
use Core\Entity\Value\EntityValueUuid;
use Core\Entity\Value\EntityValueString;

class ForumCategoriaEntity extends Entity
{
  const TABLE = 'forum_categoria';

  /** @var EntityValueUuid */
  public $uuid;
  /** @var EntityValueString */
  public $nome;
  /** @var EntityValueBolean */
  public $status;
}