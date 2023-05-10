<?php

namespace Modulo\Condominio\Repository;

use Core\Repository\BaseRepository;
use Doctrine\DBAL\Connection as DBALConnection;
use Modulo\Condominio\Entity\CondominioEntity;

class CondominioRepository
{
    /**@var DBALConnection */
    public $conn;

    public function __construct(DBALConnection $conn)
    {
        $this->conn = $conn;
    }

    public function getRepository()
    {
        return new BaseRepository($this->conn, CondominioEntity::class);
    }
}