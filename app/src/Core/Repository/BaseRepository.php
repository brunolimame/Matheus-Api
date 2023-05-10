<?php

namespace Core\Repository;

use Core\Entity\EntityColection;
use Core\Inferface\EntityInterface;
use Core\Inferface\RepositoryInterface;
use Doctrine\DBAL\Cache\CacheException;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection as DBALConnection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ForwardCompatibility\DriverStatement;
use Doctrine\DBAL\Query\QueryBuilder;

class BaseRepository implements RepositoryInterface
{
    static protected $_table;
    /** @var DBALConnection */
    static protected $_conn;
    /** @var EntityInterface|null */
    static protected $_entity;

    public function __construct(DBALConnection $conn, $entity = null)
    {
        self::$_conn   = $conn;
        self::$_entity = $entity;
        if (self::getEntity() instanceof EntityInterface) {
            self::$_table = $entity::TABLE;
        }
    }

    /**
     * @return DBALConnection|null
     */
    static public function getConn()
    {
        return self::$_conn;
    }

     /**
     * @return EntityInterface|mixed|null
     */
    static public function getEntity()
    {
        return new self::$_entity();
    }

    /**
     * @return mixed|null
     */
    static public function getEntityName()
    {
        return self::$_entity;
    }

    /**
     * @return mixed
     */
    static public function getTable()
    {
        return self::$_table;
    }

    /**
     * @param $value
     * @param string $inputWhere
     * @param array $inputSelect
     * @return mixed
     * @throws Exception
     */
    public function find($value, $inputWhere = 'uuid', $inputSelect = [])
    {
        $inputSelect = empty($inputSelect) ? '*' : $inputSelect;
        $query       = $this->getConn()->query(sprintf(
            "SELECT %s FROM %s WHERE %s='%s'",
            $inputSelect,
            self::getTable(),
            $inputWhere,
            $value
        ));
        $this->setFetchMode($query);
        return $query->fetch();
    }

    /**
     * @param array $where
     * @param array $orderBy
     * @param int $limit
     * @param int $page
     * @param array $options
     * @return EntityColection
     * @throws \Exception
     */
    public function findBy($where = [], $orderBy = [], $limit = 26, $page = 1, $options = [])
    {
        try {
            $queryBuilder = $this->getConn()->createQueryBuilder();
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
                ->from(self::getTable(), $fromAlias);

            $this
                ->hydratorLike($queryBuilder, $options)
                ->hydratorWhere($queryBuilder, $where)
                ->hydratorOrder($queryBuilder, $orderBy);

            //ADICIONAR LIMITE DE RESULTADOS E PONTO DE PARTIDA
            if ($limit) {
                $queryBuilder->setMaxResults($limit);
            }

            $resultPage = [];
            if (!is_null($page) && intval($page) > 0) {
                $page       = (int)$page;
                $resultPage = $this->factoryPagination($this->getTotalRegister($queryBuilder), $page, $limit);

                $offset = $page > 2 ? ($limit * $page) - $limit : ($page <= 1 ? 0 : $limit);
                $queryBuilder->setFirstResult($offset);
            }

            return new EntityColection((object)[
                'page'  => $resultPage,
                'itens' => $this->hydratorCache($queryBuilder, $options)
            ]);
        } catch (\Exception $e) {
            throw new \Exception(self::getEntityName() . ": " . $e->getMessage());
        }
    }

    public function findByMore($currentId, $where = [], $orderBy = [], $limit = 4, $options = [])
    {
        try {
            $queryBuilder = $this->getConn()->createQueryBuilder();
            $inputSelect  = !empty($options['select']) && is_array($options['select']) ? $options['select'] : ['*'];
            $fromAlias    = !empty($options['alias']) ? $options['alias'] : null;
            $campoId      = !empty($fromAlias) ? "{$fromAlias}.id" : "id";
            $queryBuilder->select($inputSelect)
                ->from(self::getTable(), $fromAlias)
                ->where("$campoId <> :currentId")
                ->setParameter('currentId', $currentId);

            $this
                ->hydratorLike($queryBuilder, $options)
                ->hydratorWhere($queryBuilder, $where)
                ->hydratorOrder($queryBuilder, $orderBy);

            //ADICIONAR LIMITE DE RESULTADOS E PONTO DE PARTIDA
            if ($limit) {
                $queryBuilder->setMaxResults($limit);
            }

            return new EntityColection((object)[
                'itens' => $this->hydratorCache($queryBuilder, $options)
            ]);
        } catch (\Exception $e) {
            throw new \Exception(self::getEntityName() . ": " . $e->getMessage());
        }
    }

    public function findByLastOrder($where = ['status' => 1])
    {
        try {

            $result = current($this->findBy($where, ['ordem' => 'DESC'], 1, null, ['id', 'uuid', 'ordem'])->itens);

            $payload = (object)[
                'atual'   => 0,
                'proxima' => 0
            ];

            if ($result) {
                $payload->proxima = $result->ordem->value() + 1;
                $payload->atual   = $result->ordem->value();
            }

            return $payload;
        } catch (\Exception $e) {
            throw new \Exception(self::getEntityName() . ": " . $e->getMessage());
        }
    }


    public function hydratorLike(QueryBuilder &$queryBuilder, $options = null)
    {
        if (!empty($options['busca'])) {
            $baseLike           = trim(strip_tags($options['busca']));
            $listaTermosBusca   = [];
            $listaTermosBusca[] = $baseLike;
            $listaTermosBusca[] = htmlentities($baseLike);
            $separarEspaco      = explode(" ", $baseLike);
            if (is_array($separarEspaco)) {
                array_walk($separarEspaco, function ($item) use (&$listaTermosBusca) {
                    $listaTermosBusca[] = $item;
                    $listaTermosBusca[] = htmlentities($item);
                });
            }

            array_walk($options['campos_busca'], function ($value) use (&$queryBuilder, $listaTermosBusca) {
                $likeInput  = preg_match("%([a-z_]+)\.([a-z_]+)%", $value) ? $value : "{$value}";
                $countTermo = 0;
                array_walk($listaTermosBusca, function ($termoBusca) use (&$queryBuilder, $likeInput, &$countTermo) {
                    $chave = "key{$countTermo}";
                    if (empty($queryBuilder->getQueryPart('where'))) {
                        $queryBuilder->where($queryBuilder->expr()->like($likeInput, ":{$chave}"));
                    } else {
                        $queryBuilder->orWhere($queryBuilder->expr()->like($likeInput, ":{$chave}"));
                    }
                    $queryBuilder->setParameter($chave, "%{$termoBusca}%");
                    $countTermo++;
                });
            });
        }

        return $this;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param null $where
     * @return $this
     */
    public function hydratorWhere(QueryBuilder &$queryBuilder, $where = null)
    {
        if (!empty($where)) {
            array_walk($where, function ($value, $index) use ($queryBuilder) {
                $whereInput = preg_match("%([a-z_]+)\.([a-z_]+)%", $index) ? $index : "{$index}";
                if (empty($queryBuilder->getQueryPart('where'))) {
                    $queryBuilder->where("{$whereInput} = :{$index}");
                } else {
                    $queryBuilder->andWhere("{$whereInput} = :{$index}");
                }
                $queryBuilder->setParameter(":{$index}", $value);
            });
        }
        return $this;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param null $orderBy
     * @return $this
     */
    public function hydratorOrder(QueryBuilder &$queryBuilder, $orderBy = null)
    {
        if (!empty($orderBy)) {
            array_walk($orderBy, function ($inputValue, $inputKey) use ($queryBuilder) {
                $orderInput = preg_match("%([a-z_]+)\.([a-z_]+)%", $inputKey) ? $inputKey : "{$inputKey}";
                if (empty($queryBuilder->getQueryPart('orderBy'))) {
                    $queryBuilder->orderBy($orderInput, $inputValue);
                } else {
                    $queryBuilder->addOrderBy($orderInput, $inputValue);
                }
            });
        }
        return $this;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param null $options
     * @return mixed[]|\mixed[][]
     * @throws CacheException
     * @throws Exception
     */
    public function hydratorCache(QueryBuilder &$queryBuilder, $options = null)
    {
        $cacheKey = !empty($options['cache_key']) ? $options['cache_key'] : null;
        $timeOut  = !empty($options['cache_timeout']) ? $options['cache_timeout'] : 1000;
        if (!empty($cacheKey) && is_string($cacheKey)) {
            $resultQueryExecute = $this->getConn()->executeCacheQuery(
                $queryBuilder->getSQL(),
                $queryBuilder->getParameters(),
                $queryBuilder->getParameterTypes(),
                new QueryCacheProfile($timeOut, $cacheKey)
            );
            $this->setFetchMode($resultQueryExecute);
            $resultQuery = $resultQueryExecute->fetchAll();

            $resultQueryExecute->closeCursor();
        } else {

            $executeQuery = $queryBuilder->execute();
            $this->setFetchMode($executeQuery);
            $resultQuery = $executeQuery->fetchAll();
        }

        return $resultQuery;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $input
     * @return mixed
     * @throws Exception
     */
    public function getTotalRegister(QueryBuilder &$queryBuilder, $input = 'uuid')
    {
        $queryBuilderTotal = clone $queryBuilder;
        $queryBuilderTotal->select("COUNT({$input}) as total");
        return $queryBuilderTotal->execute()->fetch()['total'];
    }

    /**
     * @param $query
     * @param null $entity
     */
    public function setFetchMode(&$query, $entity = null)
    {
        if (!is_null(self::getEntity()) || !is_null($entity)) {
            $entity = is_null($entity) ? get_class(self::getEntity()) : $entity;
            $query->setFetchMode(\PDO::FETCH_CLASS, $entity, []);
        }
    }

    /**
     *
     * @param int $total_results
     * @param int $page
     * @param int $limit
     *
     * @return object
     */
    public function factoryPagination($total_results = 0, $page = 0, $limit = 1)
    {
        $page          = (int)$page;
        $limit         = (int)$limit;
        $total_results = (int)$total_results;

        $total_pages = ($total_results <= 0 || $limit == 0) ? 1 : (int)ceil($total_results / $limit);

        $current_page = $page <= 0 ? 1 : $page;

        $next_page = $current_page + 1;

        if ($next_page >= $total_pages) {
            $next_page = $total_pages;
        }

        if ($next_page == $current_page) {
            $next_page = 0;
        }

        $before_page = $current_page - 1;

        if ($before_page <= 0) {
            $before_page = 0;
        }

        return (object)[
            "current" => $current_page,
            "next"    => $next_page,
            "before"  => $before_page,
            "total"   => $total_pages,
            "results" => $total_results,
        ];
    }

    /**
     * @param array $data
     * @return false|string
     * @throws \Exception
     */
    public function insert($data = [])
    {
        try {
            unset($data['id']);
            $queryBuilder = $this->getConn()->createQueryBuilder();
            $queryBuilder->insert(self::getTable());

            foreach (array_keys($data) as $key) {
                $queryBuilder->setValue($key, ":{$key}");
            }
            $queryBuilder->setParameters($data);

            $saveQuery = $queryBuilder->execute();

            return (bool)$saveQuery;
        } catch (\Exception $e) {
            throw new \Exception(get_class(self::getEntity()) . ": " . $e->getMessage());
        }
    }

    /**
     * @param array $where
     * @param array $data
     * @return DriverStatement|int
     * @throws \Exception
     */
    public function update($where = [], $data = [])
    {
        try {
            unset($data['id']);
            $queryBuilder = $this->getConn()->createQueryBuilder();
            $queryBuilder->update(self::getTable());

            foreach (array_keys($where) as $whereKey) {
                if (empty($queryBuilder->getQueryPart('where'))) {
                    $queryBuilder->where("{$whereKey} = :{$whereKey}");
                } else {
                    $queryBuilder->andWhere("{$whereKey} = :{$whereKey}");
                }
            }

            foreach (array_keys($data) as $entityKey) {
                $queryBuilder->set($entityKey, ":{$entityKey}");
            }
            $queryBuilder->setParameters($where + $data);

            return $queryBuilder->execute();
        } catch (\Exception $e) {
            throw new \Exception(get_class(self::getEntity()) . ": " . $e->getMessage());
        }
    }

    /**
     * @param array $where
     * @return DriverStatement|int
     * @throws \Exception
     */
    public function delete($where = [])
    {
        try {
            $queryBuilder = $this->getConn()->createQueryBuilder();
            $queryBuilder->delete(self::getTable());

            foreach (array_keys($where) as $whereKey) {
                if (empty($queryBuilder->getQueryPart('where'))) {
                    $queryBuilder->where("{$whereKey} = :{$whereKey}");
                } else {
                    $queryBuilder->andWhere("{$whereKey} = :{$whereKey}");
                }
            }

            $queryBuilder->setParameters($where);

            return $queryBuilder->execute();
        } catch (\Exception $e) {
            throw new \Exception(get_class(self::getEntity()) . ": " . $e->getMessage());
        }
    }
}
