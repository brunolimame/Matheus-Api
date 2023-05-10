<?php

namespace Modulo\Agendamento\Repository;

use Core\Repository\BaseRepository;
use Doctrine\DBAL\Connection as DBALConnection;
use Modulo\Agendamento\Entity\AgendamentoEntity;

class AgendamentoRepository
{
    /**@var DBALConnection */
    public $conn;

    public function __construct(DBALConnection $conn)
    {
        $this->conn = $conn;
    }

    public function getRepository()
    {
        return new BaseRepository($this->conn, AgendamentoEntity::class);
    }
}