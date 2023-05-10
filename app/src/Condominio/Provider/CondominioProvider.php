<?php

namespace Modulo\Condominio\Provider;

use Slim\App;
use Boot\Provider\Jwt\JwtParse;
use Laminas\Permissions\Acl\Acl;
use Core\Inferface\ProviderInterface;
use Modulo\Condominio\Provider\CondominioAclProvider;
use Modulo\Condominio\Repository\CondominioRepository;
use Modulo\Condominio\Request\CondominioRequestApi;

class CondominioProvider implements ProviderInterface
{
    static public function load(App &$app, \stdClass $args = null)
    {
        $container = $app->getContainer();
        $conexaoBD = $container->get('db:conn');
        $container->set(CondominioAclProvider::class, function (Acl $acl, JwtParse $jwtParse) {
            return new CondominioAclProvider($acl, $jwtParse);
        });
        $container->set(CondominioRequestApi::class, function () {
            return new CondominioRequestApi();
        });

        $container->set(CondominioRepository::class, function () use ($conexaoBD) {
            return new CondominioRepository($conexaoBD);
        });
    }
}
