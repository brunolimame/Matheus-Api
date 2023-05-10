<?php

namespace Modulo\Academia\Router;

use Modulo\Academia\Controller\AulaController;
use Slim\Routing\RouteCollectorProxy;

class AulaRouter
{
  static function getRoutes(RouteCollectorProxy &$router)
  {
    $aulaController = AulaController::class;
    $router->get('/aula/{curso_uuid}',                     [$aulaController, 'findAll']);
    $router->get('/aula/proximo/{curso_uuid}/{aula_uuid}', [$aulaController, 'getProximo']);
    $router->get('/aula/find/{uuid}',                      [$aulaController, 'findOne']);
    $router->post('/aula/ordenar',                         [$aulaController, 'ordenar']);
    $router->post('/aula/{curso_uuid}',                    [$aulaController, 'create']);
    $router->post('/aula/update/{aula_uuid}',              [$aulaController, 'update']);
    $router->delete('/aula/{uuid}',                        [$aulaController, 'delete']);
  }
}