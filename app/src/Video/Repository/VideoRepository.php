<?php

namespace Modulo\Video\Repository;

use Core\Repository\BaseRepository;
use Modulo\Video\Entity\VideoEntity;
use Doctrine\DBAL\Connection as DBALConnection;

class VideoRepository
{

    /**@var DBALConnection */
    public $conn;

    public function __construct(DBALConnection $conn)
    {
        $this->conn = $conn;
    }

    public function getRepository()
    {
        return new BaseRepository($this->conn, VideoEntity::class);
    }
}