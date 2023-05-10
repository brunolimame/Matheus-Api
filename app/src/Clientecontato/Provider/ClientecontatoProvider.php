<?php

namespace Modulo\Clientecontato\Provider;

use Slim\App;
use Core\Inferface\ProviderInterface;
use Modulo\Clientecontato\Repository\ClientecontatoRepository;
use Modulo\Clientecontato\Request\ClientecontatoRequestApi;

class ClientecontatoProvider implements ProviderInterface
{
    static public function load(App &$app, \stdClass $args = null)
    {
        $container = $app->getContainer();
        $conexaoBD = $container->get('db:conn');

        $container->set(ClientecontatoRequestApi::class, function () {
            return new ClientecontatoRequestApi();
        });

        $container->set(ClientecontatoRepository::class, function () use ($conexaoBD) {
            return new ClientecontatoRepository($conexaoBD);
        });
    }
}
