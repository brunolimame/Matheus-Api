<?php

namespace Modulo\Planejamento\Router;

use Modulo\Planejamento\Controller\PlanejamentoController;
use Slim\Routing\RouteCollectorProxy;

class PlanejamentoRouter
{
  public function __invoke(RouteCollectorProxy $router)
  {
    $indexController = PlanejamentoController::class;

    $router->get('/{cliente}/{mes}/{ano}', [$indexController, 'find']);

    $router->put('/{post_uuid}', [$indexController, 'toggleFeito']);
  }
}