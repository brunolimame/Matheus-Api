<?php

namespace Modulo\Setores\Repository;

use Core\Repository\BaseRepository;
use Doctrine\DBAL\Connection as DBALConnection;
use Modulo\Setores\Entity\SetoresEntity;

class SetoresRepository
{
    /**@var DBALConnection */
    public $conn;

    public function __construct(DBALConnection $conn)
    {
        $this->conn = $conn;
    }

    public function getRepository()
    {
        return new BaseRepository($this->conn, SetoresEntity::class);
    }
}