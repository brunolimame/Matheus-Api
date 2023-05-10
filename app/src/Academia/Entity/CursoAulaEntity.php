<?php

namespace Modulo\Academia\Entity;

use Core\Entity\Entity;
use Core\Entity\Value\EntityValueBolean;
use Core\Entity\Value\EntityValueInteiro;
use Core\Entity\Value\EntityValueUuid;
use Core\Entity\Value\EntityValueString;

class CursoAulaEntity extends Entity
{
  const TABLE = 'curso_aula';

  /** @var EntityValueUuid */
  public $uuid;
  /** @var EntityValueString */
  public $curso_uuid;
  /** @var EntityValueString */
  public $aula_uuid;
  /** @var EntityValueString */
  public $proximo;
  /** @var EntityValueInteiro */
  public $isprimeiro;
}