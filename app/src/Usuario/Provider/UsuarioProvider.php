<?php

namespace Modulo\Usuario\Provider;

use Slim\App;
use Slim\Views\Twig;
use Boot\Provider\Jwt\JwtParse;
use Laminas\Permissions\Acl\Acl;
use Core\Inferface\ProviderInterface;
use Modulo\Usuario\Entity\UsuarioEntity;
use Modulo\Usuario\Provider\UsuarioAclProvider;
use Modulo\Usuario\Provider\UsuarioViewProvider;
use Modulo\Usuario\Repository\UsuarioRepository;
use Boot\Provider\Email\PHPmailer\PHPmailerMessage;
use Modulo\Usuario\Event\UsuarioNotificarContaChaveEvent;
use Modulo\Usuario\Event\UsuarioNotificarContaChaveAlterarSenhaEvent;

class UsuarioProvider implements ProviderInterface
{
    static public function load(App &$app, \stdClass $args = null)
    {
        $container = $app->getContainer();
        $conexaoBD = $container->get('db:conn');

        $container->set(UsuarioViewProvider::class, function (Twig $twig) {
            return new UsuarioViewProvider($twig);
        });

        $container->set(UsuarioAclProvider::class, function (Acl $acl, JwtParse $jwtParse) {
            return new UsuarioAclProvider($acl, $jwtParse);
        });
        
        $container->set(UsuarioRepository::class, function () use ($conexaoBD) {
            return new UsuarioRepository($conexaoBD);
        });

        $container->set(UsuarioNotificarContaChaveEvent::class, function (PHPmailerMessage $PHPmailerMessage, UsuarioViewProvider $view) {
            return new UsuarioNotificarContaChaveEvent($PHPmailerMessage, $view);
        });
        
        $container->set(UsuarioNotificarContaChaveAlterarSenhaEvent::class, function (PHPmailerMessage $PHPmailerMessage, UsuarioViewProvider $view) {
            return new UsuarioNotificarContaChaveAlterarSenhaEvent($PHPmailerMessage, $view);
        });
    }
}
