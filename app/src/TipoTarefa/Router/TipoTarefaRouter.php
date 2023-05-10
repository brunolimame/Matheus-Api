<?php

namespace Modulo\TipoTarefa\Router;

use Modulo\TipoTarefa\Controller\TipoTarefaController;
use Slim\Routing\RouteCollectorProxy;

class TipoTarefaRouter
{
    public function __invoke(RouteCollectorProxy $router)
    {
        $indexController = TipoTarefaController::class;

        $router->get('', [$indexController, 'findAll'])
        ->setName('tipotarefa-findAll');

        $router->get('/getAll', [$indexController, 'getAll'])
        ->setName('tipotarefa-getAll');

        $router->get('/{uuid}', [$indexController, 'find'])
        ->setName('tipotarefa-find');

        $router->post('', [$indexController, 'create'])
        ->setName('tipotarefa-create');

        $router->put('', [$indexController, 'update'])
        ->setName('tipotarefa-update');

        $router->delete('/{uuid}', [$indexController, 'delete'])
        ->setName('tipotarefa-delete');
    }
}