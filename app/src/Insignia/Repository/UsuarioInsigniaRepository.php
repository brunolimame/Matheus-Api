<?php

namespace Modulo\Insignia\Repository;

use Core\Repository\BaseRepository;
use Doctrine\DBAL\Connection as DBALConnection;
use Modulo\Insignia\Entity\UsuarioInsigniaEntity;

class UsuarioInsigniaRepository
{
    /**@var DBALConnection */
    public $conn;

    public function __construct(DBALConnection $conn)
    {
        $this->conn = $conn;
    }

    public function getRepository()
    {
        return new BaseRepository($this->conn, UsuarioInsigniaEntity::class);
    }
}