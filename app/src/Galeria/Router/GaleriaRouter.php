<?php

namespace Modulo\Galeria\Router;

use Modulo\Galeria\Controller\GaleriaController;
use Slim\Routing\RouteCollectorProxy;

class GaleriaRouter
{
    public function __invoke(RouteCollectorProxy $router)
    {
        $indexController = GaleriaController::class;
        $router->get('', [$indexController, 'index'])
        ->setName('galeria');

    }
}