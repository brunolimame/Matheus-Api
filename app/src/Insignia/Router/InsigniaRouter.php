<?php

namespace Modulo\Insignia\Router;

use Modulo\Insignia\Controller\InsigniaController;
use Slim\Routing\RouteCollectorProxy;

class InsigniaRouter
{
    public function __invoke(RouteCollectorProxy $router)
    {
        $indexController = InsigniaController::class;

        $router->post('', [$indexController, 'index'])
        ->setName('insignia');

        $router->post('/item', [$indexController, 'getInsignia'])
            ->setName('insignia-item');

        $router->post('/getinsignias', [$indexController, 'getInsignias'])
            ->setName('insignia-getinsignias');

        $router->post('/create', [$indexController, 'create'])
            ->setName('insignia-create');

        $router->post('/set', [$indexController, 'set'])
            ->setName('insignia-set');

        $router->post('/delete', [$indexController, 'delete'])
            ->setName('insignia-delete');
    }
}