<?php

namespace Modulo\Academia\Router;

use Modulo\Academia\Controller\FaqController;
use Slim\Routing\RouteCollectorProxy;

class FaqRouter
{
  static function getRoutes(RouteCollectorProxy &$router)
  {
    $faqController = FaqController::class;
    $router->get('/faq',           [$faqController, 'findAll']);
    $router->get('/faq/{uuid}',    [$faqController, 'findOne']);
    $router->post('/faq',          [$faqController, 'create']);
    $router->post('/faq/{uuid}',   [$faqController, 'update']);
    $router->delete('/faq/{uuid}', [$faqController, 'delete']);
  }
}