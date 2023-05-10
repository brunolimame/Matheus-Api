<?php

namespace Modulo\Relatorio\Provider;

use Slim\App;
use Core\Inferface\ProviderInterface;
use Modulo\Relatorio\Repository\RelatorioRepository;
use Modulo\Relatorio\Request\RelatorioRequestApi;

class RelatorioProvider implements ProviderInterface
{
    static public function load(App &$app, \stdClass $args = null)
    {
        $container = $app->getContainer();
        $conexaoBD = $container->get('db:conn');

        $container->set(RelatorioRequestApi::class, function () {
            return new RelatorioRequestApi();
        });

        $container->set(RelatorioRepository::class, function () use ($conexaoBD) {
            return new RelatorioRepository($conexaoBD);
        });
    }
}
