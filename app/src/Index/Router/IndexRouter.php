<?php

namespace Modulo\Index\Router;

use Modulo\Index\Controller\IndexController;
use Slim\Routing\RouteCollectorProxy;

class IndexRouter
{
    public function __invoke(RouteCollectorProxy $router)
    {
        $indexController = IndexController::class;

        $router->get('', [$indexController, 'index'])
            ->setName('index');
    }
}