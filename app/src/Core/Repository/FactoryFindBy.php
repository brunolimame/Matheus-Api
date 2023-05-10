<?php

namespace Core\Repository;

use Core\Inferface\EntityInterface;
use Core\Inferface\RepositoryInterface;

class FactoryFindBy
{
    public $where   = [];
    public $order   = [];
    public $limit   = 26;
    public $page    = 0;
    public $options = [];

    static public function factory(RepositoryInterface $repository, $parametros, $definirStatus = null)
    {
        if (!empty($addParametros)) {
            $parametros = array_merge($parametros, $addParametros);
        }
        $parse = self::parse($parametros, $repository::getEntity());

        if (!is_null($definirStatus) && is_int($definirStatus)) {
            $parse->where['status'] = $definirStatus;
        }
        return $repository->findBy($parse->where, $parse->order, $parse->limit, $parse->page, $parse->options);
    }

    static public function factoryMore(RepositoryInterface $repository, $parametros)
    {
        if (empty($parametros['currentId'])) {
            throw new \InvalidArgumentException("ID do registro atual não informado");
        }
        $currentId = $parametros['currentId'];
        unset($parametros['currentId']);
        $parse = self::parse($parametros, $repository::getEntity());
        return $repository->findByMore($currentId, $parse->where, $parse->order, $parse->limit, $parse->page, $parse->options);
    }

    /**
     * @param $parametros
     * @param EntityInterface $entity
     * @return static
     */
    static public function parse($parametros, EntityInterface $entity)
    {
        //where=status#1,abc#2
        //order=id#ASC,abc#DESC
        //busca=abc
        //campos_busca=asdsasdf,sdfs,fsd,gsadf,dsf
        //campos=asd,eqwe,ad,asds,fa
        //select=*
        //limit=1
        //page=1
        //alias=12
        //cache_key=null
        //cache_timeout=null
        $factory      = new static();
        $mapaEntidade = array_keys(get_object_vars($entity));

        
        if (!empty($parametros['select'])) {
            $explodeSelect = explode(",",$parametros['select']);
            $factory->options['select'] = is_array($explodeSelect)?$explodeSelect:[$parametros['select']];
        }

        if (!empty($parametros['where'])) {
            $resultFactoryWhere = $factory::quebrarTermos($parametros['where'], []);
            array_walk($resultFactoryWhere, function ($value, $key) use ($mapaEntidade, &$factory) {
                if (in_array($key, $mapaEntidade)) {
                    $factory->where[$key] = $value;
                }
            });
        }

        if (!empty($parametros['order'])) {
            $resultFactoryOrder = $factory::quebrarTermos($parametros['order'], []);
            array_walk($resultFactoryOrder, function ($value, $key) use ($mapaEntidade, &$factory) {
                if (in_array($key, $mapaEntidade)) {
                    $factory->order[$key] = $value;
                }
            });
        } else {
            $factory->order['id'] = 'DESC';
        }

        if (!empty($parametros['alias'])) {
            $factory->options['alias'] = $parametros['alias'];
        }
        if (!empty($parametros['limit'])) {
            $factory->limit = (int)$parametros['limit'];
        }
        if (!empty($parametros['page'])) {
            $factory->page = intval($parametros['page']) > 0 ? intval($parametros['page']) : 0;
        }

        if (!empty($parametros['busca']) && !empty($parametros['campos_busca'])) {
            $factory->options['busca']        = $parametros['busca'];
            $factory->options['campos_busca'] = $parametros['campos_busca'];
        }
        if (!empty($parametros['cache_key'])) {
            $factory->options['cache_key']     = $parametros['cache_key'];
            $factory->options['cache_timeout'] = !empty($parametros['cache_timeout']) ? intval($parametros['cache_timeout']) : 1000;
        }

        if (!empty($parametros['busca']) && !empty($parametros['campos_busca'])) {
            $factory->options['busca']        = $parametros['busca'];
            $quebraCamposBusca                = explode("#", $parametros['campos_busca']);
            $factory->options['campos_busca'] = is_array($quebraCamposBusca) ? $quebraCamposBusca : [$parametros['campos_busca']];
        }

        return $factory;
    }

    static public function quebrarTermos($termos = null, $padrao = null)
    {
        if (empty($termos)) {
            return $padrao;
        }
        $exVirgula = explode(",", $termos);

        if (is_array($exVirgula)) {
            return array_reduce($exVirgula, function ($result, $termo) {
                $quebra = explode("#", $termo);
                if (is_array($quebra)) {
                    $result[$quebra[0]] = $quebra[1];
                }
                return $result;
            }, []);
        } else {
            $termo  = [];
            $quebra = explode("#", $termos);
            if (is_array($quebra)) {
                $termo[$quebra[0]] = $quebra[1];
            }
            return $termo;
        }
    }
}