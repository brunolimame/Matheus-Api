<?php

namespace Modulo\Versao\Router;

use Modulo\Versao\Controller\VersaoController;
use Slim\Routing\RouteCollectorProxy;

class VersaoRouter
{
    public function __invoke(RouteCollectorProxy $router)
    {
        $indexController = VersaoController::class;

        $router->get('', [$indexController, 'find']);
        $router->put('', [$indexController, 'update']);
    }
}