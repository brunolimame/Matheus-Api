<?php

namespace Modulo\Tarefa\Router;

use Modulo\Tarefa\Controller\TarefaController;
use Slim\Routing\RouteCollectorProxy;

class TarefaRouter
{
  public function __invoke(RouteCollectorProxy $router)
  {
    $indexController = TarefaController::class;

    $router->get('/{uuid}', [$indexController, 'find']);
    $router->get('/{data}/{nivel}/{setor}/{colaborador_uuid}', [$indexController, 'findAll']);
    $router->get('/{data}/{nivel}/{setor}', [$indexController, 'findAll']);
    $router->post('', [$indexController, 'create']);
    $router->put('/{uuid}', [$indexController, 'update']);
  }
}