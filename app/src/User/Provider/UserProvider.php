<?php

namespace Modulo\User\Provider;

use Slim\App;
use Boot\Provider\Jwt\JwtParse;
use Laminas\Permissions\Acl\Acl;
use Core\Inferface\ProviderInterface;
use Modulo\User\Provider\UserAclProvider;
use Modulo\User\Repository\UserRepository;
use Modulo\User\Request\UserRequestApi;

class UserProvider implements ProviderInterface
{
    static public function load(App &$app, \stdClass $args = null)
    {
        $container = $app->getContainer();
        $conexaoBD = $container->get('db:conn');
        $container->set(UserAclProvider::class, function (Acl $acl, JwtParse $jwtParse) {
            return new UserAclProvider($acl, $jwtParse);
        });
        $container->set(UserRequestApi::class, function () {
            return new UserRequestApi();
        });

        $container->set(UserRepository::class, function () use ($conexaoBD) {
            return new UserRepository($conexaoBD);
        });
    }
}
