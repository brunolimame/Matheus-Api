<?php

namespace Modulo\Condominio\Router;

use Modulo\Condominio\Controller\CondominioController;
use Modulo\Condominio\Middleware\CondominioAuthMiddleware;
use Slim\Routing\RouteCollectorProxy;

class CondominioRouter
{
    public function __invoke(RouteCollectorProxy $router)
    {
        $indexController = CondominioController::class;

        $router->get('', [$indexController, 'index'])
        ->setName('condominio');

        $router->post('', [$indexController, 'salvar'])
            ->setName('condominio-salvar');
//            ->add(CondominioAuthMiddleware::class);

        $router->put('/status/{status:[a|d]}', [$indexController, 'status'])
            ->setName('condominio-status')
            ->add(CondominioAuthMiddleware::class);

        $router->delete('', [$indexController, 'delete'])
            ->setName('condominio-delete')
            ->add(CondominioAuthMiddleware::class);
    }
}