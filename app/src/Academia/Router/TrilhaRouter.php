<?php

namespace Modulo\Academia\Router;

use Modulo\Academia\Controller\TrilhaController;
use Slim\Routing\RouteCollectorProxy;

class TrilhaRouter
{
  static function getRoutes(RouteCollectorProxy &$router)
  {
    $trilhaController = TrilhaController::class;
    $router->get('/trilha',           [$trilhaController, 'findAll']);
    $router->get('/trilha/{uuid}',    [$trilhaController, 'find']);
    $router->post('/trilha',          [$trilhaController, 'create']);
    $router->put('/trilha/{uuid}',    [$trilhaController, 'update']);
    $router->delete('/trilha/{uuid}', [$trilhaController, 'delete']);
  }
}