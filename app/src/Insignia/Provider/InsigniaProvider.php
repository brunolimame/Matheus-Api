<?php

namespace Modulo\Insignia\Provider;

use Modulo\Insignia\Repository\UsuarioInsigniaRepository;
use Slim\App;
use Core\Inferface\ProviderInterface;
use Modulo\Insignia\Repository\InsigniaRepository;
use Modulo\Insignia\Request\InsigniaRequestApi;

class InsigniaProvider implements ProviderInterface
{
    static public function load(App &$app, \stdClass $args = null)
    {
        $container = $app->getContainer();
        $conexaoBD = $container->get('db:conn');

        $container->set(InsigniaRequestApi::class, function () {
            return new InsigniaRequestApi();
        });

        $container->set(InsigniaRepository::class, function () use ($conexaoBD) {
            return new InsigniaRepository($conexaoBD);
        });
        $container->set(UsuarioInsigniaRepository::class, function () use ($conexaoBD) {
            return new UsuarioInsigniaRepository($conexaoBD);
        });
    }
}
