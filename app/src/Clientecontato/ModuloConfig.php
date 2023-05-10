<?php

namespace Modulo\Clientecontato;

use Modulo\Clientecontato\Provider\ClientecontatoProvider;
use Core\Inferface\ModuloConfigInterface;
use Modulo\Clientecontato\Router\ClientecontatoRouter;

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
                'clientecontato' => ClientecontatoRouter::class
            ],
            'provider'   => [
                ClientecontatoProvider::class => []
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
