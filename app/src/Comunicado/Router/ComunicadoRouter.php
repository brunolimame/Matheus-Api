<?php

namespace Modulo\Comunicado\Router;

use Modulo\Comunicado\Controller\ComunicadoController;
use Slim\Routing\RouteCollectorProxy;

class ComunicadoRouter
{
    public function __invoke(RouteCollectorProxy $router)
    {
        $indexController = ComunicadoController::class;

        $router->get('', [$indexController, 'getComunicados'])
            ->setName('get-comunicados');

        $router->get('/getall', [$indexController, 'getAllComunicados'])
            ->setName('get-all-comunicados');

        $router->post('/save', [$indexController, 'salvar'])
            ->setName('comunicado-salvar');

        $router->post('/item', [$indexController, 'getComunicado'])
            ->setName('comunicado-item');

        $router->post('/status', [$indexController, 'status'])
            ->setName('comunicado-status');

        $router->post('/fixo', [$indexController, 'fixo'])
            ->setName('comunicado-fixo');

        $router->post('/delete', [$indexController, 'delete'])
            ->setName('comunicado-delete');
    }
}