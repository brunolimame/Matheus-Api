<?php

namespace Modulo\Agendamento\Provider;

use Slim\App;
use Core\Inferface\ProviderInterface;
use Modulo\Agendamento\Repository\AgendamentoRepository;
use Modulo\Agendamento\Request\AgendamentoRequestApi;

class AgendamentoProvider implements ProviderInterface
{
    static public function load(App &$app, \stdClass $args = null)
    {
        $container = $app->getContainer();
        $conexaoBD = $container->get('db:conn');

        $container->set(AgendamentoRequestApi::class, function () {
            return new AgendamentoRequestApi();
        });

        $container->set(AgendamentoRepository::class, function () use ($conexaoBD) {
            return new AgendamentoRepository($conexaoBD);
        });
    }
}
