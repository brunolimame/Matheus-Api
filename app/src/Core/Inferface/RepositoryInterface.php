<?php

namespace Core\Inferface;

use Doctrine\DBAL\Connection as DBALConnection;

interface RepositoryInterface
{
    /** @return DBALConnection|null */
    static public function getConn();

    /** @return EntityInterface|null */
    static public function getEntity();

    static public function getTable();

    public function find($id, $campos = []);

    public function findBy($where = [], $orderBy = [], $limit = 26, $page = 0, $options = []);

    public function findByMore($currentId, $where = [], $orderBy = [], $limit = 4, $optionsw = []);

    public function findByLastOrder($where = []);

    public function insert($data = []);

    public function update($where = [], $data = []);

    public function delete($where = []);

}