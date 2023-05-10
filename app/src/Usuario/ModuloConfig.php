<?php

namespace Modulo\Usuario;

use Core\Inferface\ModuloConfigInterface;
use Modulo\Usuario\Event\UsuarioNotificarContaChaveEvent;
use Modulo\Usuario\Provider\UsuarioProvider;
use Modulo\Usuario\Router\UsuarioApiRouter;
use Modulo\Usuario\Router\UsuarioRouter;

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
                'json'  => [
                    'usuario' => UsuarioApiRouter::class
                ]
            ],
            'provider'   => [
                UsuarioProvider::class => []
            ],
            'acl' => [
                'usuario' => [
                    'usuario' => ['minha-senha'],
                    'moderador' => ['minha-senha']
                ]
            ]
        ];
    }
}
