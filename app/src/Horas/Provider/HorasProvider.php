<?php

namespace Modulo\Horas\Provider;

use Slim\App;
use Core\Inferface\ProviderInterface;
use Modulo\Horas\Repository\HorasRepository;

class HorasProvider implements ProviderInterface
{
    static public function load(App &$app, \stdClass $args = null)
    {
        $container = $app->getContainer();
        $conexaoBD = $container->get('db:conn');

        $container->set(HorasRepository::class, function () use ($conexaoBD) {
            return new HorasRepository($conexaoBD);
        });
    }
}
