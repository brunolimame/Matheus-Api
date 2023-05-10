<?php

namespace Modulo\Cliente;

use Modulo\Cliente\Provider\ClienteProvider;
use Core\Inferface\ModuloConfigInterface;
use Modulo\Cliente\Router\ClienteRouter;

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
                'cliente' => ClienteRouter::class
            ],
            'provider'   => [
                ClienteProvider::class => []
            ],
            'middleware' => [],
            'event'      => [],
            'acl' => [
                'cliente' => [
                    'convidado' => ['ler'],
                    'usuario' => ['auth','ler-todos', 'novo', 'editar', 'status', 'ordem','legenda'],
                    'moderador' => ['remover']
                ]
            ]
        ];
    }
}
