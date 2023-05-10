<?php

namespace Modulo\Academia\Router;

use Modulo\Academia\Controller\UsuarioAulaController;
use Slim\Routing\RouteCollectorProxy;

class UsuarioAulaRouter
{
  static function getRoutes(RouteCollectorProxy &$router)
  {
    $usuarioAulaController = UsuarioAulaController::class;
    $router->get('/usuarioaula/{usuario_uuid}/{aula_uuid}', [$usuarioAulaController, 'findOne']);
    $router->post('/usuarioaula',                           [$usuarioAulaController, 'create']);
    $router->put('/usuarioaula/{usuario_uuid}/{aula_uuid}', [$usuarioAulaController, 'setConcluido']);
//    $router->post('/usuarioaula/{uuid}',                            [$usuarioAulaController, 'update']);
    $router->delete('/usuarioaula/{uuid}',                  [$usuarioAulaController, 'delete']);
  }
}