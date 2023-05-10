<?php

namespace Modulo\Galeria\Router;

use Slim\Routing\RouteCollectorProxy;
use Modulo\Galeria\Controller\GaleriaApiController;
use Modulo\Galeria\Middleware\GaleriaAuthMiddleware;
use Modulo\Galeria\Middleware\GaleriaSalvarMiddleware;
use Modulo\Galeria\Controller\GaleriaFotoApiController;
use Modulo\Galeria\Middleware\GaleriaFotoSalvarMiddleware;

class GaleriaApiRouter
{
    public function __invoke(RouteCollectorProxy $router)
    {
        $indexController     = GaleriaApiController::class;
        $indexControllerFoto = GaleriaFotoApiController::class;

        $router->get('', [$indexController, 'index'])
            ->setName('api-galeria');

        $router->post('', [$indexController, 'salvar'])
            ->setName('api-galeria-salvar')
            ->add(GaleriaAuthMiddleware::class)
            ->add(GaleriaSalvarMiddleware::class);

        $router->put('/status/{status:[a|d]}', [$indexController, 'status'])
            ->setName('api-galeria-status')
            ->add(GaleriaAuthMiddleware::class);

        $router->delete('', [$indexController, 'delete'])
            ->setName('api-galeria-delete')
            ->add(GaleriaAuthMiddleware::class);


        $router->get('/foto', [$indexControllerFoto, 'index'])
            ->setName('api-galeria-foto');

        $router->post('/foto', [$indexControllerFoto, 'salvar'])
            ->setName('api-galeria-foto-salvar')
            ->add(GaleriaAuthMiddleware::class)
            ->add(GaleriaFotoSalvarMiddleware::class);

        $router->put('/foto/ordem', [$indexControllerFoto, 'ordem'])
            ->setName('api-galeria-foto-ordem')
            ->add(GaleriaAuthMiddleware::class);

        $router->put('/foto/status/{status:[a|d]}', [$indexControllerFoto, 'status'])
            ->setName('api-galeria-foto-status')
            ->add(GaleriaAuthMiddleware::class);

        $router->post('/foto/legenda', [$indexControllerFoto, 'legenda'])
            ->setName('api-galeria-foto-legenda')
            ->add(GaleriaAuthMiddleware::class);

        $router->delete('/foto', [$indexControllerFoto, 'delete'])
            ->setName('api-galeria-foto-delete')
            ->add(GaleriaAuthMiddleware::class);

    }
}