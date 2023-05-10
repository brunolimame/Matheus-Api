<?php

namespace Modulo\Tarefa\Repository;

use Core\Entity\EntityColection;
use Core\Repository\BaseRepository;
use Doctrine\DBAL\Connection as DBALConnection;
use Exception;
use Modulo\Tarefa\Entity\TarefaEntity;

class TarefaRepository
{
  /**@var DBALConnection */
  public $conn;

  public function __construct(DBALConnection $conn)
  {
    $this->conn = $conn;
  }

  public function getRepository(): BaseRepository
  {
    return new BaseRepository($this->conn, TarefaEntity::class);
  }

  /**
   * @throws Exception
   */
  public function findAtrasados($setor, $data, $user_uuid = null): EntityColection
  {
    try {
      $queryBuilder = $this->conn->createQueryBuilder();

      $queryBuilder
        ->select(['*'])
        ->from(TarefaEntity::TABLE)
        ->where('setor = :setor')
        ->andWhere('data < :dataString')
        ->andWhere('status <> :concluida')
        ->andWhere('status <> :recusada')
        ->setParameter(':setor', $setor)
        ->setParameter(':dataString', $data)
        ->setParameter(':concluida', "concluida")
        ->setParameter(':recusada', "recusada")
      ;

      if ($user_uuid) {
        $queryBuilder
          ->andWhere('user_uuid = :user_uuid')
          ->setParameter(':user_uuid', $user_uuid)
        ;
      }

      $queryBuilder->orderBy('data', 'ASC');

      return new EntityColection((object)['itens' => $this->getRepository()->hydratorCache($queryBuilder)]);
    } catch (Exception $e) {
      throw new Exception((new TarefaEntity())->getEntityName() . ": " . $e->getMessage());
    }
  }
}