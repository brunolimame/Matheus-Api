<?php

namespace Modulo\Cliente\Provider;

use Slim\App;
use Boot\Provider\Jwt\JwtParse;
use Laminas\Permissions\Acl\Acl;
use Core\Inferface\ProviderInterface;
use Modulo\Cliente\Provider\ClienteAclProvider;
use Modulo\Cliente\Repository\ClienteRepository;
use Modulo\Cliente\Request\ClienteRequestApi;

class ClienteProvider implements ProviderInterface
{
    static public function load(App &$app, \stdClass $args = null)
    {
        $container = $app->getContainer();
        $conexaoBD = $container->get('db:conn');
        $container->set(ClienteAclProvider::class, function (Acl $acl, JwtParse $jwtParse) {
            return new ClienteAclProvider($acl, $jwtParse);
        });
        $container->set(ClienteRequestApi::class, function () {
            return new ClienteRequestApi();
        });

        $container->set(ClienteRepository::class, function () use ($conexaoBD) {
            return new ClienteRepository($conexaoBD);
        });
    }
}
