<?php

namespace Modulo\Comunicado\Provider;

use Slim\App;
use Core\Inferface\ProviderInterface;
use Modulo\Comunicado\Repository\ComunicadoRepository;
use Modulo\Comunicado\Request\ComunicadoRequestApi;

class ComunicadoProvider implements ProviderInterface
{
    static public function load(App &$app, \stdClass $args = null)
    {
        $container = $app->getContainer();
        $conexaoBD = $container->get('db:conn');

        $container->set(ComunicadoRequestApi::class, function () {
            return new ComunicadoRequestApi();
        });

        $container->set(ComunicadoRepository::class, function () use ($conexaoBD) {
            return new ComunicadoRepository($conexaoBD);
        });
    }
}
