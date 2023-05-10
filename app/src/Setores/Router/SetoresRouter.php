<?php

namespace Modulo\Setores\Router;

use Modulo\Setores\Controller\SetoresController;
use Slim\Routing\RouteCollectorProxy;

class SetoresRouter
{
    public function __invoke(RouteCollectorProxy $router)
    {
        $indexController = SetoresController::class;

        $router->get('',           [$indexController, 'findAll']);
        $router->get('/{uuid}',    [$indexController, 'find']);
        $router->post('',          [$indexController, 'create']);
        $router->put('',           [$indexController, 'update']);
        $router->delete('/{uuid}', [$indexController, 'delete']);
    }
}