<?php

namespace Modulo\Galeria\Provider;

use Slim\App;
use Slim\Views\Twig;
use Boot\Provider\Jwt\JwtParse;
use Laminas\Permissions\Acl\Acl;
use Core\Inferface\ProviderInterface;
use Modulo\Galeria\Provider\GaleriaAclProvider;
use Modulo\Galeria\Provider\GaleriaViewProvider;
use Modulo\Galeria\Repository\GaleriaRepository;
use Modulo\Galeria\Request\GaleriaFotoRequestApi;
use Modulo\Galeria\Repository\GaleriaFotoRepository;
use Modulo\Galeria\Request\GaleriaRequestApi;

class GaleriaProvider implements ProviderInterface
{
    static public function load(App &$app, \stdClass $args = null)
    {
        $container = $app->getContainer();
        $conexaoBD = $container->get('db:conn');
        $container->set(GaleriaAclProvider::class, function (Acl $acl, JwtParse $jwtParse) {
            return new GaleriaAclProvider($acl, $jwtParse);
        });
        $container->set(GaleriaRequestApi::class, function () {
            return new GaleriaRequestApi();
        });
        $container->set(GaleriaFotoRequestApi::class, function () {
            return new GaleriaFotoRequestApi();
        });

        $container->set(GaleriaRepository::class, function () use ($conexaoBD) {
            return new GaleriaRepository($conexaoBD);
        });
        
        $container->set(GaleriaFotoRepository::class, function () use ($conexaoBD) {
            return new GaleriaFotoRepository($conexaoBD);
        });
        
        $container->set(GaleriaViewProvider::class, function (Twig $twig) {
            return new GaleriaViewProvider($twig);
        });
    }
}
