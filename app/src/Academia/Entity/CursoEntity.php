<?php

namespace Modulo\Academia\Entity;

use Core\Entity\Entity;
use Core\Entity\Value\EntityValueBolean;
use Core\Entity\Value\EntityValueLog;
use Core\Entity\Value\EntityValueUuid;
use Core\Entity\Value\EntityValueString;
use Core\Lib\ParametrosArquivo;

class CursoEntity extends Entity
{
  const TABLE = 'curso';

  /** @var EntityValueUuid */
  public $uuid;
  /** @var EntityValueString */
  public $nome;
  /** @var EntityValueString */
  public $capa;
  /** @var EntityValueString */
  public $descricao;
  /** @var EntityValueLog */
  public $log;
  /** @var EntityValueBolean */
  public $status;

  static public function getParametrosParaArquivo()
  {
    return ParametrosArquivo::load(
      "/assets/curso/",
      10
    );
  }
}