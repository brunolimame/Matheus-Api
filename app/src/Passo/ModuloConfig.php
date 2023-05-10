<?php

namespace Modulo\Passo;

use Modulo\Passo\Provider\PassoProvider;
use Core\Inferface\ModuloConfigInterface;
use Modulo\Passo\Router\PassoRouter;

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
                'passo' => PassoRouter::class
            ],
            'provider'   => [
                PassoProvider::class => []
            ],
            'middleware' => [],
            'event'      => [],
            'acl' => [
                'passo' => [
                    'convidado' => ['ler'],
                    'usuario' => ['auth','ler-todos', 'novo', 'editar', 'status', 'ordem','legenda'],
                    'moderador' => ['remover']
                ]
            ]
        ];
    }
}
