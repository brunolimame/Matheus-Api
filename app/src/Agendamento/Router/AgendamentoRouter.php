<?php

namespace Modulo\Agendamento\Router;

use Modulo\Agendamento\Controller\AgendamentoController;
use Slim\Routing\RouteCollectorProxy;

class AgendamentoRouter
{
  public function __invoke(RouteCollectorProxy $router)
  {
    $indexController = AgendamentoController::class;

    $router->get('/{dia}/{mes}/{ano}', [$indexController, 'findAll']);

    $router->get('/{uuid}', [$indexController, 'find']);

    $router->post('', [$indexController, 'create']);

    $router->put('', [$indexController, 'update']);

    $router->put('/{dia}/{mes}/{ano}', [$indexController, 'reorder']);

    $router->delete('/{uuid}', [$indexController, 'delete']);
  }
}