<?php

namespace Modulo\User\Router;

use Modulo\User\Controller\UserController;
use Slim\Routing\RouteCollectorProxy;

class UserRouter
{
    public function __invoke(RouteCollectorProxy $router)
    {
        $indexController = UserController::class;

        $router->post('', [$indexController, 'index'])
        ->setName('user');

        $router->get('/all', [$indexController, 'all'])
        ->setName('user');

        $router->post('/save', [$indexController, 'salvar'])
            ->setName('user-salvar');

        $router->post('/item', [$indexController, 'index'])
            ->setName('user-item');

        $router->get('/item/{uuid}', [$indexController, 'findOne']);

        $router->get('/designer', [$indexController, 'getDesigner'])
            ->setName('user-get-designer');

        $router->get('/colaboradores', [$indexController, 'getColaboradores'])
            ->setName('user-get-colaboradores');

        $router->post('/status', [$indexController, 'status'])
            ->setName('user-status');

        $router->post('/delete', [$indexController, 'delete'])
            ->setName('user-delete');
    }
}