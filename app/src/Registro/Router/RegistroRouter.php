<?php

namespace Modulo\Registro\Router;

use Modulo\Registro\Controller\Controller;
use Slim\Routing\RouteCollectorProxy;

class RegistroRouter
{
  public function __invoke(RouteCollectorProxy $router)
  {
    $indexController = Controller::class;

    $router->get('',          [$indexController, 'findAll']);
    $router->get('/{uuid}',    [$indexController, 'findOne']);
    $router->post('',         [$indexController, 'create']);
    $router->post('/{uuid}',   [$indexController, 'update']);
    $router->delete('/{uuid}', [$indexController, 'delete']);
  }
}