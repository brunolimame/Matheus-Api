<?php

namespace Modulo\Post\Repository;

use Core\Repository\BaseRepository;
use Doctrine\DBAL\Connection as DBALConnection;
use Modulo\Post\Entity\PostEntity;

class PostRepository
{
    /**@var DBALConnection */
    public $conn;

    public function __construct(DBALConnection $conn)
    {
        $this->conn = $conn;
    }

    public function getRepository()
    {
        return new BaseRepository($this->conn, PostEntity::class);
    }
}