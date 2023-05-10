<?php

namespace Modulo\Horas\Router;

use Modulo\Horas\Controller\HorasController;
use Slim\Routing\RouteCollectorProxy;

class HorasRouter
{
  public function __invoke(RouteCollectorProxy $router)
  {
    $indexController = HorasController::class;

    $router->get('/quantidade/{data}', [$indexController, 'quantidade']);
    $router->get('/tempo/{data}', [$indexController, 'tempo']);
    $router->get('/alteracao/{data}', [$indexController, 'alteracao']);
    $router->get('/intervalo/{data_inicio}/{data_fim}/{cliente_uuid}', [$indexController, 'intervalo']);
    $router->get('/intervalo/{data_inicio}/{data_fim}/{cliente_uuid}/{designer_uuid}', [$indexController, 'intervalo']);
  }
}