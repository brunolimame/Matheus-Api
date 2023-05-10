<?php

namespace Modulo\Versao\Provider;

use Slim\App;
use Core\Inferface\ProviderInterface;
use Modulo\Versao\Repository\VersaoRepository;
use Modulo\Versao\Request\VersaoRequestApi;

class VersaoProvider implements ProviderInterface
{
    static public function load(App &$app, \stdClass $args = null)
    {
        $container = $app->getContainer();
        $conexaoBD = $container->get('db:conn');

        $container->set(VersaoRequestApi::class, function () {
            return new VersaoRequestApi();
        });

        $container->set(VersaoRepository::class, function () use ($conexaoBD) {
            return new VersaoRepository($conexaoBD);
        });
    }
}
