<?php

namespace Modulo\Planejamento\Provider;

use Slim\App;
use Boot\Provider\Jwt\JwtParse;
use Laminas\Permissions\Acl\Acl;
use Core\Inferface\ProviderInterface;
use Modulo\Planejamento\Provider\PlanejamentoAclProvider;
use Modulo\Planejamento\Repository\PlanejamentoRepository;
use Modulo\Planejamento\Request\PlanejamentoRequestApi;

class PlanejamentoProvider implements ProviderInterface
{
    static public function load(App &$app, \stdClass $args = null)
    {
        $container = $app->getContainer();
        $conexaoBD = $container->get('db:conn');
        $container->set(PlanejamentoAclProvider::class, function (Acl $acl, JwtParse $jwtParse) {
            return new PlanejamentoAclProvider($acl, $jwtParse);
        });
        $container->set(PlanejamentoRequestApi::class, function () {
            return new PlanejamentoRequestApi();
        });

        $container->set(PlanejamentoRepository::class, function () use ($conexaoBD) {
            return new PlanejamentoRepository($conexaoBD);
        });
    }
}
