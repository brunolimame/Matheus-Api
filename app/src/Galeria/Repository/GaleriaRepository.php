<?php

namespace Modulo\Galeria\Repository;

use Core\Repository\BaseRepository;
use Doctrine\DBAL\Connection as DBALConnection;
use Modulo\Galeria\Entity\GaleriaEntity;

class GaleriaRepository
{

    /**@var DBALConnection */
    public $conn;


    public function __construct(DBALConnection $conn)
    {
        $this->conn = $conn;
    }

    public function getRepository()
    {
        return new BaseRepository($this->conn, GaleriaEntity::class);
    }
}