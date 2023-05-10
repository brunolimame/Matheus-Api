<?php

namespace Modulo\Passo\Repository;

use Core\Repository\BaseRepository;
use Doctrine\DBAL\Connection as DBALConnection;
use Modulo\Passo\Entity\PassoArquivoEntity;

class PassoArquivoRepository
{
    /**@var DBALConnection */
    public $conn;

    public function __construct(DBALConnection $conn)
    {
        $this->conn = $conn;
    }

    public function getRepository()
    {
        return new BaseRepository($this->conn, PassoArquivoEntity::class);
    }
}