<?php

namespace Modulo\Post;

use Modulo\Post\Provider\PostProvider;
use Core\Inferface\ModuloConfigInterface;
use Modulo\Post\Router\PostRouter;

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
                'post' => PostRouter::class
            ],
            'provider'   => [
                PostProvider::class => []
            ],
            'middleware' => [],
            'event'      => [],
            'acl' => [
                'post' => [
                    'convidado' => ['ler'],
                    'usuario' => ['auth','ler-todos', 'novo', 'editar', 'status', 'ordem','legenda'],
                    'moderador' => ['remover']
                ]
            ]
        ];
    }
}
