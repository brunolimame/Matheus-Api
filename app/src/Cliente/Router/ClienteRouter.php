<?php

namespace Modulo\Cliente\Router;

use Modulo\Cliente\Controller\ClienteController;
use Slim\Routing\RouteCollectorProxy;

class ClienteRouter
{
  public function __invoke(RouteCollectorProxy $router)
  {
    $indexController = ClienteController::class;

    $router->get('', [$indexController, 'findAll']);

    $router->get('/{uuid}', [$indexController, 'find']);

    $router->get('/pagina/{pagina}/{designer_uuid}', [$indexController, 'findByPage']);

    $router->get('/pagina/{pagina}/{designer_uuid}/{busca}', [$indexController, 'findByPage']);

    $router->post('', [$indexController, 'create']);

    $router->post('/{uuid}', [$indexController, 'update']);

    $router->put('/status/{uuid}', [$indexController, 'status']);

    $router->delete('/{uuid}', [$indexController, 'delete']);
  }
}