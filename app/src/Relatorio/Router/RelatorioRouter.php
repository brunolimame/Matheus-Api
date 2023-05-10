<?php

namespace Modulo\Relatorio\Router;

use Modulo\Relatorio\Controller\RelatorioController;
use Slim\Routing\RouteCollectorProxy;

class RelatorioRouter
{
  public function __invoke(RouteCollectorProxy $router)
  {
    $indexController = RelatorioController::class;

    $router->get('/designer/{modo}/{ano}/{mes}', [$indexController, 'findAll']);
    $router->get('/designer/{modo}/{ano}/{mes}/{colaborador}', [$indexController, 'findAll']);
  }
}