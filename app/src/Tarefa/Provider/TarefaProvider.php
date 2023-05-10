<?php

namespace Modulo\Tarefa\Provider;

use Slim\App;
use Core\Inferface\ProviderInterface;
use Modulo\Tarefa\Repository\TarefaRepository;
use Modulo\Tarefa\Request\TarefaRequestApi;

class TarefaProvider implements ProviderInterface
{
    static public function load(App &$app, \stdClass $args = null)
    {
        $container = $app->getContainer();
        $conexaoBD = $container->get('db:conn');

        $container->set(TarefaRequestApi::class, function () {
            return new TarefaRequestApi();
        });

        $container->set(TarefaRepository::class, function () use ($conexaoBD) {
            return new TarefaRepository($conexaoBD);
        });
    }
}
