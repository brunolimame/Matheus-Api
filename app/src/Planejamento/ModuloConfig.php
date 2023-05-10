<?php

namespace Modulo\Planejamento;

use Modulo\Planejamento\Provider\PlanejamentoProvider;
use Core\Inferface\ModuloConfigInterface;
use Modulo\Planejamento\Router\PlanejamentoRouter;

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
                'planejamento' => PlanejamentoRouter::class
            ],
            'provider'   => [
                PlanejamentoProvider::class => []
            ],
            'middleware' => [],
            'event'      => [],
            'acl' => [
                'planejamento' => [
                    'convidado' => ['ler'],
                    'usuario' => ['auth','ler-todos', 'novo', 'editar', 'status', 'ordem','legenda'],
                    'moderador' => ['remover']
                ]
            ]
        ];
    }
}
