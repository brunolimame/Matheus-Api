<?php

namespace Modulo\Index\Router;

use Doctrine\DBAL\Connection;
use Modulo\Index\Controller\IndexApiController;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest as Request;
use Slim\Routing\RouteCollectorProxy;

class IndexApiRouter
{
    public function __invoke(RouteCollectorProxy $router)
    {
        $indexController = IndexApiController::class;
        $router->get('', [$indexController, 'index'])
            ->setName('api-index');

    }
}