<?php

namespace Modulo\TipoTarefa;

use Modulo\TipoTarefa\Provider\TipoTarefaProvider;
use Core\Inferface\ModuloConfigInterface;
use Modulo\TipoTarefa\Router\TipoTarefaRouter;

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
                'tipotarefa' => TipoTarefaRouter::class
            ],
            'provider'   => [
                TipoTarefaProvider::class => []
            ],
            'middleware' => [],
            'event'      => [],
        ];
    }
}
