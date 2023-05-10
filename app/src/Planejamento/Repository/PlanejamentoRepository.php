<?php

namespace Modulo\Planejamento\Repository;

use Core\Repository\BaseRepository;
use Doctrine\DBAL\Connection as DBALConnection;
use Modulo\Planejamento\Entity\PlanejamentoEntity;

class PlanejamentoRepository
{
    /**@var DBALConnection */
    public $conn;

    public function __construct(DBALConnection $conn)
    {
        $this->conn = $conn;
    }

    public function getRepository()
    {
        return new BaseRepository($this->conn, PlanejamentoEntity::class);
    }
}