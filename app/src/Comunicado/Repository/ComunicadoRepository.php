<?php

namespace Modulo\Comunicado\Repository;

use Core\Entity\EntityColection;
use Core\Repository\BaseRepository;
use Doctrine\DBAL\Connection as DBALConnection;
use Exception;
use Modulo\Comunicado\Entity\ComunicadoEntity;

class ComunicadoRepository
{
    /**@var DBALConnection */
    public $conn;

    public function __construct(DBALConnection $conn)
    {
        $this->conn = $conn;
    }

    public function getRepository()
    {
        return new BaseRepository($this->conn, ComunicadoEntity::class);
    }

    /**
     * @param string $uuid
     * @param bool $criacao
     * @param bool $desenvolvimento
     * @param bool $fotografia
     * @param bool $atendimento
     * @return EntityColection
     * @throws Exception
     */
    public function findByUuidOrSetor(string $uuid, bool $criacao = false, bool $desenvolvimento = false, bool $fotografia = false, bool $atendimento = false): EntityColection
    {
        try {
            $queryBuilder = $this->conn->createQueryBuilder();
            $queryBuilder
                ->select('*')
                ->from($this->getRepository()->getTable())
                ->where($queryBuilder->expr()->isNull('setor'))
                ->andWhere($queryBuilder->expr()->isNull('user_uuid'))
                ->orWhere('FIND_IN_SET(:uuid, user_uuid)')
                ->setParameter(':uuid', $uuid)
            ;

//            $setores = array_filter([
//                'criacao'=>$criacao,
//                'desenvolvimento'=>$desenvolvimento,
//                'fotografia'=>$fotografia,
//                'atendimento'=>$atendimento
//            ]);
//            if(!empty($setores) && is_array($setores) && count($setores)>0){
//                $queryBuilder->orWhere($queryBuilder->expr()->in()'FIND_IN_SET("criacao", setor)');
//            }
            if ($criacao) {
                $queryBuilder->orWhere('FIND_IN_SET("criacao", setor)');
            }

            if ($desenvolvimento) {
                $queryBuilder->orWhere('FIND_IN_SET("desenvolvimento", setor)');
            }

            if ($fotografia) {
                $queryBuilder->orWhere('FIND_IN_SET("fotografia", setor)');
            }

            if ($atendimento) {
                $queryBuilder->orWhere('FIND_IN_SET("atendimento", setor)');
            }

            $queryBuilder
                ->andWhere('status = 1')
                ->orderBy('fixo', 'DESC')
                ->addOrderBy('criado', 'DESC')
                ->setMaxResults(50);

            return new EntityColection((object)[
                'itens' => $this->getRepository()->hydratorCache($queryBuilder)
            ]);
        } catch (Exception $e) {
            throw new Exception($this->getRepository()->getEntityName() . ": " . $e->getMessage());
        }
    }
}