<?php

namespace Modulo\Administrativo;

use Modulo\Administrativo\Provider\AdministrativoProvider;
use Core\Inferface\ModuloConfigInterface;
use Modulo\Administrativo\Router\AdministrativoRouter;

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
                'admin' => AdministrativoRouter::class
            ],
            'provider'   => [
                AdministrativoProvider::class => []
            ],
            'middleware' => [],
            'event'      => [],
            'acl' => [
                'admin' => [
                    'convidado' => ['ler'],
                    'usuario' => ['auth','ler-todos', 'novo', 'editar', 'status', 'ordem','legenda'],
                    'moderador' => ['remover']
                ]
            ]
        ];
    }
}
