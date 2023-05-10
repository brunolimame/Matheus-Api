<?php

namespace Modulo\Video\Provider;

use Slim\App;
use Slim\Views\Twig;
use Boot\Provider\Jwt\JwtParse;
use Laminas\Permissions\Acl\Acl;
use Core\Inferface\ProviderInterface;
use Modulo\Video\Request\VideoRequestApi;
use Modulo\Video\Provider\VideoAclProvider;
use Modulo\Video\Provider\VideoViewProvider;
use Modulo\Video\Repository\VideoRepository;

class VideoProvider implements ProviderInterface
{
    static public function load(App &$app, \stdClass $args = null)
    {
        $container = $app->getContainer();
        $conexaoBD = $container->get('db:conn');
        
        $container->set(VideoAclProvider::class, function (Acl $acl, JwtParse $jwtParse) {
            return new VideoAclProvider($acl, $jwtParse);
        });
        $container->set(VideoRequestApi::class, function () {
            return new VideoRequestApi();
        });
        
        $container->set(VideoRepository::class, function () use ($conexaoBD) {
            return new VideoRepository($conexaoBD);
        });

        $container->set(VideoViewProvider::class, function (Twig $twig) {
            return new VideoViewProvider($twig);
        });
    }

}