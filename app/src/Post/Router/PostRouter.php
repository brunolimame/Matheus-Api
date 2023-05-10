<?php

namespace Modulo\Post\Router;

use Modulo\Post\Controller\PostController;
use Slim\Routing\RouteCollectorProxy;

class PostRouter
{
  public function __invoke(RouteCollectorProxy $router)
  {
    $indexController = PostController::class;

    $router->get('/{uuid}', [$indexController, 'find']);

    $router->get('/quantidade/{mes}', [$indexController, 'quantidade']);

    $router->post('', [$indexController, 'create']);

    $router->put('/{uuid}', [$indexController, 'update']);

    $router->put('/status/{uuid}', [$indexController, 'status']);

    $router->delete('/{uuid}', [$indexController, 'delete']);
  }
}