<?php

namespace Modulo\Academia\Entity;

use Core\Entity\Entity;
use Core\Entity\Value\EntityValueBolean;
use Core\Entity\Value\EntityValueUuid;
use Core\Entity\Value\EntityValueString;

class FaqEntity extends Entity
{
  const TABLE = 'faq';

  /** @var EntityValueUuid */
  public $uuid;
  /** @var EntityValueString */
  public $pergunta;
  /** @var EntityValueString */
  public $resposta;
  /** @var EntityValueBolean */
  public $status;
}