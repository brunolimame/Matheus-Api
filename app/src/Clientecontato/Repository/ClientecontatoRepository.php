<?php

namespace Modulo\Clientecontato\Repository;

use Core\Repository\BaseRepository;
use Doctrine\DBAL\Connection as DBALConnection;
use Modulo\Clientecontato\Entity\ClientecontatoEntity;

class ClientecontatoRepository
{
    /**@var DBALConnection */
    public $conn;

    public function __construct(DBALConnection $conn)
    {
        $this->conn = $conn;
    }

    public function getRepository()
    {
        return new BaseRepository($this->conn, ClientecontatoEntity::class);
    }
}