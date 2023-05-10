<?php

namespace Modulo\Setores\Provider;

use Slim\App;
use Core\Inferface\ProviderInterface;
use Modulo\Setores\Repository\SetoresRepository;
use Modulo\Setores\Request\SetoresRequestApi;

class SetoresProvider implements ProviderInterface
{
    static public function load(App &$app, \stdClass $args = null)
    {
        $container = $app->getContainer();
        $conexaoBD = $container->get('db:conn');

        $container->set(SetoresRequestApi::class, function () {
            return new SetoresRequestApi();
        });

        $container->set(SetoresRepository::class, function () use ($conexaoBD) {
            return new SetoresRepository($conexaoBD);
        });
    }
}
