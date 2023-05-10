<?php

namespace Modulo\TipoTarefa\Repository;

use Core\Repository\BaseRepository;
use Doctrine\DBAL\Connection as DBALConnection;
use Modulo\TipoTarefa\Entity\TipoTarefaEntity;

class TipoTarefaRepository
{
    /**@var DBALConnection */
    public $conn;

    public function __construct(DBALConnection $conn)
    {
        $this->conn = $conn;
    }

    public function getRepository()
    {
        return new BaseRepository($this->conn, TipoTarefaEntity::class);
    }
}