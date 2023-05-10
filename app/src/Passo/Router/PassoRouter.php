<?php

namespace Modulo\Passo\Router;

use Modulo\Passo\Controller\PassoController;
use Slim\Routing\RouteCollectorProxy;

class PassoRouter
{
    public function __invoke(RouteCollectorProxy $router)
    {
        $indexController = PassoController::class;

        $router->get('/{uuid}', [$indexController, 'find']);

        $router->post('/{tarefa_uuid}', [$indexController, 'create']);
    }
}