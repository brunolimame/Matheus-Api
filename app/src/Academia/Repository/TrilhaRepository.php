<?php

namespace Modulo\Academia\Repository;

use Modulo\Academia\Entity\TrilhaEntity;
use Core\Repository\BaseRepository;
use Doctrine\DBAL\Connection as DBALConnection;
class TrilhaRepository
{
  /**@var DBALConnection */
  public $conn;

  public function __construct(DBALConnection $conn)
  {
    $this->conn = $conn;
  }
  public function getRepository(): BaseRepository
  {
    return new BaseRepository($this->conn, TrilhaEntity::class);
  }
}