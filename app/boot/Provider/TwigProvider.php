<?php

namespace Boot\Provider;

use Boot\Provider\Twig\Filter\TwigExtra;
use Core\Inferface\ProviderInterface;
use Slim\App;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Twig\Extension\DebugExtension;

class TwigProvider implements ProviderInterface
{

    static public function load(App &$app, \stdClass $args = null)
    {
        $args->debug = !is_bool($args->debug) ? $args->debug : true;
        $args->path  = !empty($args->path) ? $args->path : $_SERVER['DOCUMENT_ROOT'] . '/./../app/src';
        $args->cache = !empty($args->cache) ? $args->cache : $_SERVER['DOCUMENT_ROOT'] . '/./../app/storage/twig';

        $container = $app->getContainer();
        $container->set(Twig::class, function () use ($args, &$container) {
            $twig = Twig::create($args->path, [
                'debug' => $args->debug,
                'cache' => $args->cache
            ]);

            if ($args->debug) {
                $twig->addExtension(new DebugExtension());
            }
            $twig->addExtension(new TwigExtra($container));

            return $twig;
        });

        $container->set('view', function () use (&$container) {
            return $container->get(Twig::class);
        });

        $app->add(TwigMiddleware::createFromContainer($app));
    }
}
