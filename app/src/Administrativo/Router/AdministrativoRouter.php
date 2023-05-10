<?php

namespace Modulo\Administrativo\Router;

use Modulo\Administrativo\Controller\AdministrativoController;
use Slim\Routing\RouteCollectorProxy;

class AdministrativoRouter
{
    public function __invoke(RouteCollectorProxy $router)
    {
        $indexController = AdministrativoController::class;

        $router->get('', [$indexController, 'index'])
        ->setName('administrativo');

        $router->post('', [$indexController, 'indexMes'])
            ->setName('administrativo-mes');

        $router->post('/media', [$indexController, 'getMedia'])
            ->setName('administrativo-media');

        $router->post('/getmes', [$indexController, 'getMes'])
            ->setName('administrativo-getmes');

        $router->post('/setmes', [$indexController, 'setMes'])
            ->setName('administrativo-setmes');

        $router->post('/gettabela', [$indexController, 'getTabela'])
            ->setName('administrativo-gettabela');
    }
}