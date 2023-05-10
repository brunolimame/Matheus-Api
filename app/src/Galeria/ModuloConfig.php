<?php

namespace Modulo\Galeria;

use Modulo\Galeria\Provider\GaleriaProvider;
use Modulo\Galeria\Router\GaleriaApiRouter;
use Core\Inferface\ModuloConfigInterface;
use Core\Inferface\RouterInterface;
use Modulo\Galeria\Router\GaleriaFotoApiRouter;
use Modulo\Galeria\Router\GaleriaRouter;

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
                'galeria' => GaleriaRouter::class,
                'json'    => [
                    'galeria' => GaleriaApiRouter::class
                ]
            ],
            'provider'   => [
                GaleriaProvider::class => []
            ],
            'middleware' => [],
            'event'      => [],
            'acl' => [
                'galeria' => [
                    'convidado' => ['ler'],
                    'usuario' => ['auth','ler-todos', 'novo', 'editar', 'status', 'ordem','legenda'],
                    'moderador' => ['remover']
                ]
            ]
        ];
    }
}
