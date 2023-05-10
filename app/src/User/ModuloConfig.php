<?php

namespace Modulo\User;

use Modulo\User\Provider\UserProvider;
use Core\Inferface\ModuloConfigInterface;
use Modulo\User\Router\UserRouter;

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
                'user' => UserRouter::class
            ],
            'provider'   => [
                UserProvider::class => []
            ],
            'middleware' => [],
            'event'      => [],
            'acl' => [
                'user' => [
                    'convidado' => ['ler'],
                    'usuario' => ['auth','ler-todos', 'novo', 'editar', 'status', 'ordem','legenda'],
                    'moderador' => ['remover']
                ]
            ]
        ];
    }
}
