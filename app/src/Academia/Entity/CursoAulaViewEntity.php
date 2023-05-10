<?php

namespace Modulo\Academia\Entity;

use Core\Entity\Entity;
use Core\Entity\Value\EntityValueInteiro;
use Core\Entity\Value\EntityValueString;

class CursoAulaViewEntity extends Entity
{
  const TABLE = 'curso_aula_view';

  /** @var EntityValueString */
  public $uuid;
  /** @var EntityValueString */
  public $curso_uuid;
  /** @var EntityValueString */
  public $aula_uuid;
  /** @var EntityValueString */
  public $aula_nome;
  /** @var EntityValueString */
  public $aula_descricao;
  /** @var EntityValueString */
  public $proximo;
  /** @var EntityValueInteiro */
  public $isprimeiro;
  /** @var EntityValueString */
  public $usuario_uuid;
  /** @var EntityValueInteiro */
  public $aula_concluida;
}