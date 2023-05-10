<?php

namespace Modulo\Agendamento;

use Modulo\Agendamento\Provider\AgendamentoProvider;
use Core\Inferface\ModuloConfigInterface;
use Modulo\Agendamento\Router\AgendamentoRouter;

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
                'agendamento' => AgendamentoRouter::class
            ],
            'provider'   => [
                AgendamentoProvider::class => []
            ],
            'middleware' => [],
            'event'      => [],
        ];
    }
}
