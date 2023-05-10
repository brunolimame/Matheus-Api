<?php

namespace Modulo\Usuario\Router;

use Nyholm\Psr7\Response;
use Doctrine\DBAL\Connection;
use Slim\Routing\RouteCollectorProxy;
use Nyholm\Psr7\ServerRequest as Request;
use Modulo\Usuario\Controller\UsuarioApiController;
use Modulo\Usuario\Middleware\UsuarioAuthMiddleware;
use Modulo\Usuario\Middleware\UsuarioSalvarMiddleware;
use Modulo\Usuario\Middleware\UsuarioVerificarEmailMiddleware;
use Modulo\Usuario\Middleware\UsuarioVerificarSenhaMiddleware;
use Modulo\Usuario\Middleware\UsuarioVerificarAcessoMiddleware;
use Modulo\Usuario\Middleware\UsuarioVerificarUsernameMiddleware;

class UsuarioApiRouter
{
    public function __invoke(RouteCollectorProxy $router)
    {
        $indexController = UsuarioApiController::class;

        $router->get('', [$indexController, 'index'])
            ->setName('api-usuario')
            ->add(UsuarioAuthMiddleware::class);

        $router->post('', [$indexController, 'salvar'])
            ->setName('api-usuario-salvar')
            ->add(UsuarioAuthMiddleware::class)
            ->add(UsuarioSalvarMiddleware::class);
        
        $router->delete('', [$indexController, 'delete'])
            ->setName('api-usuario-delete')
            ->add(UsuarioAuthMiddleware::class);

        $router->get('/temacesso', [$indexController, 'temAcesso'])
            ->setName('api-usuario-teste-acesso')
            ->add(UsuarioVerificarAcessoMiddleware::class);

        $router->get('/niveis', [$indexController, 'listaNiveis'])
            ->setName('api-niveis')
            ->add(UsuarioAuthMiddleware::class);

        $router->post('/senha', [$indexController, 'senha'])
            ->setName('api-usuario-senha')
            ->add(UsuarioAuthMiddleware::class)
            ->add(UsuarioVerificarSenhaMiddleware::class);

        $router->get('/verificar/username', [$indexController, 'verificarUsername'])
            ->setName('api-usuario-verificar-username')
            ->add(UsuarioAuthMiddleware::class)
            ->add(UsuarioVerificarUsernameMiddleware::class);

        $router->get('/verificar/email', [$indexController, 'verificarEmail'])
            ->setName('api-usuario-verificar-email')
            ->add(UsuarioAuthMiddleware::class)
            ->add(UsuarioVerificarEmailMiddleware::class);
    }
}
