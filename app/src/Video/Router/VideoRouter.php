<?php

namespace Modulo\Video\Router;

use Modulo\Video\Controller\VideoController;
use Slim\Routing\RouteCollectorProxy;

class VideoRouter
{
    public function __invoke(RouteCollectorProxy $router)
    {
        $indexController = VideoController::class;
        $router->get('', [$indexController, 'index'])
        ->setName('video');
    }
}