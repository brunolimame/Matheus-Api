<?php

namespace Modulo\Comunicado;

use Modulo\Comunicado\Provider\ComunicadoProvider;
use Core\Inferface\ModuloConfigInterface;
use Modulo\Comunicado\Router\ComunicadoRouter;

class ModuloConfig implements ModuloConfigInterface
{
    static public function isEnable(): bool
    {
        return true;
    }

    static public function getConf(): array
    {
        return [
            'router'     => [
                'comunicado' => ComunicadoRouter::class
            ],
            'provider'   => [
                ComunicadoProvider::class => []
            ],
            'middleware' => [],
            'event'      => []
        ];
    }
}
