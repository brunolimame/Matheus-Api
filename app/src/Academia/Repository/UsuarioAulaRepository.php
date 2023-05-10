<?php

namespace Modulo\Academia\Repository;

use Modulo\Academia\Entity\UsuarioAulaEntity;
use Core\Repository\BaseRepository;
use Doctrine\DBAL\Connection as DBALConnection;
class UsuarioAulaRepository
{
  /**@var DBALConnection */
  public $conn;

  public function __construct(DBALConnection $conn)
  {
    $this->conn = $conn;
  }
  public function getRepository(): BaseRepository
  {
    return new BaseRepository($this->conn, UsuarioAulaEntity::class);
  }

}