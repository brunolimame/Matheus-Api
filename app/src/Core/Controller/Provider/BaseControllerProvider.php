<?php

namespace Core\Controller\Provider;

use Slim\App;
use Core\Controller\Lib\ControllerRedirect;
use Core\Inferface\ProviderInterface;

class BaseControllerProvider implements ProviderInterface
{
    static public function load(App &$app, \stdClass $args = null)
    {
        $container = $app->getContainer();
            
        $container->set(ControllerRedirect::class, function () use(&$app) {
            return new ControllerRedirect($app->getResponseFactory());
        });
    }
}