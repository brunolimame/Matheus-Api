<?php

namespace Modulo\Academia\Repository;

use Modulo\Academia\Entity\CursoEntity;
use Core\Repository\BaseRepository;
use Doctrine\DBAL\Connection as DBALConnection;
class CursoRepository
{
  /**@var DBALConnection */
  public $conn;

  public function __construct(DBALConnection $conn)
  {
    $this->conn = $conn;
  }
  public function getRepository(): BaseRepository
  {
    return new BaseRepository($this->conn, CursoEntity::class);
  }

}