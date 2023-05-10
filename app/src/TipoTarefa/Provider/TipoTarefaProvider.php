<?php

namespace Modulo\TipoTarefa\Provider;

use Slim\App;
use Core\Inferface\ProviderInterface;
use Modulo\TipoTarefa\Repository\TipoTarefaRepository;
use Modulo\TipoTarefa\Request\TipoTarefaRequestApi;

class TipoTarefaProvider implements ProviderInterface
{
    static public function load(App &$app, \stdClass $args = null)
    {
        $container = $app->getContainer();
        $conexaoBD = $container->get('db:conn');

        $container->set(TipoTarefaRequestApi::class, function () {
            return new TipoTarefaRequestApi();
        });

        $container->set(TipoTarefaRepository::class, function () use ($conexaoBD) {
            return new TipoTarefaRepository($conexaoBD);
        });
    }
}
