<?php

namespace Modulo\Index\Provider;

use Slim\App;
use Slim\Views\Twig;
use Core\Inferface\ProviderInterface;
use Modulo\Index\Provider\IndexViewProvider;

class IndexProvider implements ProviderInterface
{
    
    static public function load(App &$app, \stdClass $args = null)
    {
        $container = $app->getContainer();

        $container->set(IndexViewProvider::class, function (Twig $twig) {
            return new IndexViewProvider($twig,'/Index/View/');
        });
    
    }

}