<?php

namespace Modulo\Administrativo\Provider;

use Modulo\Administrativo\Repository\ConfigMesesRepository;
use Slim\App;
use Boot\Provider\Jwt\JwtParse;
use Laminas\Permissions\Acl\Acl;
use Core\Inferface\ProviderInterface;
use Modulo\Administrativo\Provider\AdministrativoAclProvider;
use Modulo\Administrativo\Repository\AdministrativoRepository;
use Modulo\Administrativo\Request\AdministrativoRequestApi;

class AdministrativoProvider implements ProviderInterface
{
    static public function load(App &$app, \stdClass $args = null)
    {
        $container = $app->getContainer();
        $conexaoBD = $container->get('db:conn');
        $container->set(AdministrativoAclProvider::class, function (Acl $acl, JwtParse $jwtParse) {
            return new AdministrativoAclProvider($acl, $jwtParse);
        });
        $container->set(AdministrativoRequestApi::class, function () {
            return new AdministrativoRequestApi();
        });

        $container->set(AdministrativoRepository::class, function () use ($conexaoBD) {
            return new AdministrativoRepository($conexaoBD);
        });

        $container->set(ConfigMesesRepository::class, function () use ($conexaoBD) {
            return new ConfigMesesRepository($conexaoBD);
        });
    }
}
