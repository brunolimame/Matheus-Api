<?php

namespace Boot\Provider\Doctrine;

use Core\Inferface\ProviderInterface;
use Doctrine\DBAL\DriverManager;
use Slim\App;

class DoctrineProvider implements ProviderInterface
{
    static public function load(App &$app, \stdClass $args = null)
    {

        $container = $app->getContainer();

        $listaConexoes = get_object_vars($args);
        array_walk($listaConexoes, function ($parametros, $nome) use (&$container) {
            $container->set("db:$nome", self::factoryConecao($parametros));
        });
    }

    static protected function factoryConecao($parametros = [])
    {
        $parametros['url']           = !empty($parametros['url']) ? $parametros['url'] : null;
        $parametros['driver']        = !empty($parametros['driver']) ? $parametros['driver'] : 'pdo_mysql';
        $parametros['host']          = !empty($parametros['host']) ? $parametros['host'] : 'localhost';
        $parametros['port']          = !empty($parametros['port']) ? $parametros['port'] : 3306;
        $parametros['dbname']        = !empty($parametros['dbname']) ? $parametros['dbname'] : 'painel_v2';
        $parametros['user']          = !empty($parametros['user']) ? $parametros['user'] : 'root';
        $parametros['password']      = !empty($parametros['password']) ? $parametros['password'] : '';
        $parametros['charset']       = !empty($parametros['charset']) ? $parametros['charset'] : 'utf8mb4';
        $parametros['options']       = !empty($parametros['options']) ? $parametros['options'] : null;
        $parametros['cache']         = !empty($parametros['cache']) ? $parametros['cache'] : null;
        $parametros['cache_options'] = !empty($parametros['cache_options']) ? $parametros['cache_options'] : [];

        $driverEnable = [
            "pdo_mysql", "drizzle_pdo_mysql", "mysqli", "pdo_sqlite", "pdo_pgsql", "pdo_oci",
            "pdo_sqlsrv", "sqlsrv", "oci8", "sqlanywhere"
        ];

        if (!in_array($parametros['driver'], $driverEnable)) {
            throw new \InvalidArgumentException(sprintf("O driver '%s' é inválido para o Doctrine", $parametros['driver']));
        }

        $parametrosDeConexao = $parametros;
        unset($parametrosDeConexao['options']);
        unset($parametrosDeConexao['cache']);
        unset($parametrosDeConexao['cache_options']);
        $conn = DriverManager::getConnection($parametrosDeConexao, $parametros['options']);

        $cacheClass   = $parametros['cache'];
        $cacheOptions = $parametros['cache_options'];
        if (class_exists($cacheClass) && !empty($cacheClass)) {
            $cacheConfig = empty($cacheOptions) ? new $cacheClass() : new $cacheClass($cacheOptions);
            $configConn  = $conn->getConfiguration();
            $configConn->setResultCacheImpl($cacheConfig);
        }
        return $conn;
    }
}