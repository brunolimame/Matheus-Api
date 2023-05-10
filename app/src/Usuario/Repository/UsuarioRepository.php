<?php

namespace Modulo\Usuario\Repository;

use Core\Repository\BaseRepository;
use Modulo\Usuario\Entity\UsuarioEntity;
use Doctrine\DBAL\Connection as DBALConnection;

class UsuarioRepository
{
    /**@var DBALConnection */
    public $conn;
     
    public function __construct(DBALConnection $conn)
    {
        $this->conn = $conn;

    }

    public function getRepository()
    {
        return new BaseRepository($this->conn, UsuarioEntity::class);
    }

    public function findByUsernameEmail($username, $todosOsDados = false)
    {
        $repository = $this->getRepository();
        try {
            $queryBuilder = $repository->getConn()->createQueryBuilder();

            $select = $todosOsDados ? ["i.*"] : ['i.uuid', 'i.nome', 'i.foto', 'i.username', 'i.email', 'i.password', 'i.salt', 'i.nivel'];
            $queryBuilder->select($select)
                ->from($repository->getTable(), 'i')
                ->where('i.username = :username')
                ->orWhere('i.email = :username')
                ->andWhere('i.status = 1')
                ->setParameter('username', $username);

            return current($repository->hydratorCache($queryBuilder));
        } catch (\Exception $e) {
            throw new \Exception($repository::getEntityName() . ": " . $e->getMessage());
        }
    }

    public function verificarUsername($username, $uuid = null)
    {
        $repository = $this->getRepository();
        try {
            $queryBuilder = $repository->getConn()->createQueryBuilder();

            $queryBuilder->select(['i.uuid'])
                ->from($repository->getTable(), 'i')
                ->where('i.username = :username')
                ->setParameter('username', $username);

            if (!empty($uuid)) {
                $queryBuilder
                    ->andWhere('i.uuid != :uuid')
                    ->setParameter('uuid', $uuid);
            }

            return current($repository->hydratorCache($queryBuilder));
        } catch (\Exception $e) {
            throw new \Exception($repository::getEntityName() . ": " . $e->getMessage());
        }
    }

    public function verificarEmail($email, $uuid = null)
    {
        $repository = $this->getRepository();
        try {
            $queryBuilder = $repository->getConn()->createQueryBuilder();

            $queryBuilder->select(['i.uuid'])
                ->from($repository->getTable(), 'i')
                ->where('i.email = :email')
                ->setParameter('email', $email);

            if (!empty($uuid)) {
                $queryBuilder
                    ->andWhere('i.uuid != :uuid')
                    ->setParameter('uuid', $uuid);
            }

            return current($repository->hydratorCache($queryBuilder));
        } catch (\Exception $e) {
            throw new \Exception($repository::getEntityName() . ": " . $e->getMessage());
        }
    }
}
