<?php

namespace Modulo\Academia\Repository;

use Core\Repository\BaseRepository;
use Doctrine\DBAL\Connection as DBALConnection;
use Modulo\Academia\Entity\CursoAulaViewEntity;

class CursoAulaViewRepository
{
  /**@var DBALConnection */
  public $conn;

  public function __construct(DBALConnection $conn)
  {
    $this->conn = $conn;
  }
  public function getRepository(): BaseRepository
  {
    return new BaseRepository($this->conn, CursoAulaViewEntity::class);
  }

}