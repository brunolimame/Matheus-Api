<?php

namespace Modulo\Clientecontato\Router;

use Modulo\Clientecontato\Controller\ClientecontatoController;
use Slim\Routing\RouteCollectorProxy;

class ClientecontatoRouter
{
    public function __invoke(RouteCollectorProxy $router)
    {
        $indexController = ClientecontatoController::class;

        $router->post('', [$indexController, 'getContato'])
            ->setName('get-contato');

        $router->post('/save', [$indexController, 'setContato'])
            ->setName('save-contato');

        $router->post('/delete', [$indexController, 'delete'])
            ->setName('delete-contato');
    }
}