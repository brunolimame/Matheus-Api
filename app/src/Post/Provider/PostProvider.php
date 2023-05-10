<?php

namespace Modulo\Post\Provider;

use Slim\App;
use Boot\Provider\Jwt\JwtParse;
use Laminas\Permissions\Acl\Acl;
use Core\Inferface\ProviderInterface;
use Modulo\Post\Provider\PostAclProvider;
use Modulo\Post\Repository\PostRepository;
use Modulo\Post\Request\PostRequestApi;

class PostProvider implements ProviderInterface
{
    static public function load(App &$app, \stdClass $args = null)
    {
        $container = $app->getContainer();
        $conexaoBD = $container->get('db:conn');
        $container->set(PostAclProvider::class, function (Acl $acl, JwtParse $jwtParse) {
            return new PostAclProvider($acl, $jwtParse);
        });
        $container->set(PostRequestApi::class, function () {
            return new PostRequestApi();
        });

        $container->set(PostRepository::class, function () use ($conexaoBD) {
            return new PostRepository($conexaoBD);
        });
    }
}
