<?php

namespace Modulo\Academia\Router;

use Modulo\Academia\Controller\ForumController;
use Slim\Routing\RouteCollectorProxy;

class ForumRouter
{
  static function getRoutes(RouteCollectorProxy &$router)
  {
    $forumController = ForumController::class;
    $router->get('/forum/categoria',         [$forumController, 'findAllCategorias']);
    $router->get('/forum/categoria/{uuid}',  [$forumController, 'findOneCategoria']);
    $router->post('/forum/categoria',        [$forumController, 'createCategoria']);
    $router->post('/forum/categoria/{uuid}', [$forumController, 'updateCategoria']);

    $router->get('/forum',           [$forumController, 'findAll']);
    $router->get('/forum/{uuid}',    [$forumController, 'findOne']);
    $router->post('/forum',          [$forumController, 'create']);
    $router->post('/forum/{uuid}',   [$forumController, 'update']);
    $router->delete('/forum/{uuid}', [$forumController, 'delete']);
  }
}