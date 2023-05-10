<?php

namespace Modulo\Registro\Provider;

use Slim\App;
use Core\Inferface\ProviderInterface;
use Modulo\Registro\Repository\Repository;
use Modulo\Registro\Request\RegistroRequestApi;

class RegistroProvider implements ProviderInterface
{
    static public function load(App &$app, \stdClass $args = null)
    {
        $container = $app->getContainer();
        $conexaoBD = $container->get('db:conn');

        $container->set(RegistroRequestApi::class, function () {
            return new RegistroRequestApi();
        });

        $container->set(Repository::class, function () use ($conexaoBD) {
            return new Repository($conexaoBD);
        });
    }
}
