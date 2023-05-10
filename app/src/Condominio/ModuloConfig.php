<?php

namespace Modulo\Condominio;

use Modulo\Condominio\Provider\CondominioProvider;
use Core\Inferface\ModuloConfigInterface;
use Modulo\Condominio\Router\CondominioRouter;

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
                'condominio' => CondominioRouter::class
            ],
            'provider'   => [
                CondominioProvider::class => []
            ],
            'middleware' => [],
            'event'      => [],
            'acl' => [
                'condominio' => [
                    'convidado' => ['ler'],
                    'usuario' => ['auth','ler-todos', 'novo', 'editar', 'status', 'ordem','legenda'],
                    'moderador' => ['remover']
                ]
            ]
        ];
    }
}
