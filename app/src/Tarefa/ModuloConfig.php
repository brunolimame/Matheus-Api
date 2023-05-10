<?php

namespace Modulo\Tarefa;

use Modulo\Tarefa\Provider\TarefaProvider;
use Core\Inferface\ModuloConfigInterface;
use Modulo\Tarefa\Router\TarefaRouter;

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
                'tarefa' => TarefaRouter::class
            ],
            'provider'   => [
                TarefaProvider::class => []
            ],
            'middleware' => [],
            'event'      => [],
        ];
    }
}
