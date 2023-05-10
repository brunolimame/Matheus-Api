<?php

namespace Modulo\Academia\Router;

use Modulo\Academia\Controller\CursoController;
use Slim\Routing\RouteCollectorProxy;

class CursoRouter
{
  static function getRoutes(RouteCollectorProxy &$router)
  {
    $cursoController = CursoController::class;
    $router->get('/curso',             [$cursoController, 'findAll']);
    $router->get('/curso/{nivel}',     [$cursoController, 'findAll']);
    $router->get('/curso/find/{uuid}', [$cursoController, 'findOne']);
    $router->post('/curso',            [$cursoController, 'create']);
    $router->post('/curso/{uuid}',     [$cursoController, 'update']);
    $router->delete('/curso/{uuid}',   [$cursoController, 'delete']);
  }
}