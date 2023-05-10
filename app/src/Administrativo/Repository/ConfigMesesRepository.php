<?php

namespace Modulo\Administrativo\Repository;

use Core\Repository\BaseRepository;
use Doctrine\DBAL\Connection as DBALConnection;
use Modulo\Administrativo\Entity\ConfigMesesEntity;

class ConfigMesesRepository
{
    /**@var DBALConnection */
    public $conn;

    public function __construct(DBALConnection $conn)
    {
        $this->conn = $conn;
    }

    public function getRepository()
    {
        return new BaseRepository($this->conn, ConfigMesesEntity::class);
    }
}