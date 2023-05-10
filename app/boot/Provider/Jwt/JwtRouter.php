<?php

namespace Boot\Provider\Jwt;

use Slim\Routing\RouteCollectorProxy;
use Modulo\Usuario\Controller\UsuarioApiController;
use Modulo\Usuario\Middleware\UsuarioLoginRecuperarMiddleware;

class JwtRouter
{
    public function __invoke(RouteCollectorProxy $router)
    {
        $indexController = JwtController::class;
        $usuarioController = UsuarioApiController::class;
        $router->post('', [$indexController, 'auth'])
            ->setName('auth');

        $router->map(['GET','POST'],'/validar', [$indexController, 'validarTokens'])
            ->setName('auth-validar');

        $router->post('/login', [$indexController, 'login'])
            ->setName('auth-login')
            ->add(JwtLoginMiddleware::class);
        
        $router->post('/recuperar', [$usuarioController, 'recuperar'])
            ->setName('auth-recuperar')
            ->add(UsuarioLoginRecuperarMiddleware::class);
        
        $router->get('/decode', [$indexController, 'decode'])
            ->setName('auth-decode');

        $router->post('/refresh', [$indexController, 'refresh'])
            ->setName('auth-refresh');
    }
}