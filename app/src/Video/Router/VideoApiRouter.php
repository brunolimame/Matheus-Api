<?php

namespace Modulo\Video\Router;

use Slim\Routing\RouteCollectorProxy;
use Modulo\Video\Controller\VideoApiController;
use Modulo\Video\Middleware\VideoAuthMiddleware;
use Modulo\Video\Middleware\VideoSalvarMiddleware;

class VideoApiRouter
{
    public function __invoke(RouteCollectorProxy $router)
    {
        $indexController = VideoApiController::class;

        $router->get('', [$indexController, 'index'])
            ->setName('api-video');

        $router->post('', [$indexController, 'salvar'])
            ->setName('api-video-salvar')
            ->add(VideoAuthMiddleware::class)
            ->add(VideoSalvarMiddleware::class);
        
        $router->delete('', [$indexController, 'delete'])
            ->setName('api-video-delete')
            ->add(VideoAuthMiddleware::class);

        $router->put('/status/{status:[a|d]}', [$indexController, 'status'])
            ->setName('api-video-status')
            ->add(VideoAuthMiddleware::class);
        
        $router->get('/locais', [$indexController, 'local'])
            ->setName('api-local')
            ->add(VideoAuthMiddleware::class);

        $router->get('/live', [$indexController, 'live'])
            ->setName('api-live');
    }
}