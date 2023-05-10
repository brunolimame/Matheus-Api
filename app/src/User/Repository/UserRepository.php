<?php

namespace Modulo\User\Repository;

use Core\Entity\EntityColection;
use Core\Repository\BaseRepository;
use Doctrine\DBAL\Connection as DBALConnection;
use Modulo\User\Entity\UserEntity;

class UserRepository
{
    /**@var DBALConnection */
    public $conn;

    public function __construct(DBALConnection $conn)
    {
        $this->conn = $conn;
    }

    public function getRepository()
    {
        return new BaseRepository($this->conn, UserEntity::class);
    }

    public function findByDesigner()
    {
        $repo = $this->getRepository();

        try {
            $queryBuilder = $repo->getConn()->createQueryBuilder();
            $inputSelect = ['*'];
            if(!empty($options['select'])){
                if(is_array($options['select'])){
                    $inputSelect = $options['select'];
                }else{
                    $explodeSelect = explode(",",$options['select']);
                    $inputSelect = is_array($explodeSelect)? $explodeSelect:[$options['select']];
                }
            }
            $fromAlias    = !empty($options['alias']) ? $options['alias'] : null;
            $queryBuilder->select($inputSelect)
                ->from($repo::getTable(), $fromAlias)
//                ->where('nivel IN("designer","planner")')
                ->where('status = :status')
                ->andWhere("FIND_IN_SET('designer', nivel)")
                ->orderBy('nome', 'ASC')
                ->setParameter(':status', 1)
//                ->setParameter(':campo', "%designer%")
                ->setMaxResults(25);


            return new EntityColection((object)[
                'itens' => $repo->hydratorCache($queryBuilder)
            ]);
        } catch (\Exception $e) {
            throw new \Exception($repo::getEntityName() . ": " . $e->getMessage());
        }
    }
}