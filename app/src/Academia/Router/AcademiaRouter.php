<?php

namespace Modulo\Academia\Router;

use Slim\Routing\RouteCollectorProxy;

class AcademiaRouter
{
  public function __invoke(RouteCollectorProxy $router)
  {
    AulaRouter::getRoutes($router);
    CursoRouter::getRoutes($router);
    FaqRouter::getRoutes($router);
    ForumRouter::getRoutes($router);
    TrilhaRouter::getRoutes($router);
    UsuarioAulaRouter::getRoutes($router);
  }
}