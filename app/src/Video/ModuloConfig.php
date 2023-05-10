<?php

namespace Modulo\Video;

use Modulo\Video\Provider\VideoProvider;
use Modulo\Video\Router\VideoApiRouter;
use Core\Inferface\ModuloConfigInterface;
use Modulo\Video\Router\VideoRouter;

class ModuloConfig implements ModuloConfigInterface
{

    static public function isEnable():bool
    {
        return true;
    }

    static public function getConf():array
    {
        return [
            'router'     => [
                'video' => VideoRouter::class,
                'json'  => [
                    'video' => VideoApiRouter::class
                ]
            ],
            'provider'   => [
                VideoProvider::class => []
            ],
            'middleware' => [],
            'event'      => [],
            'acl' => [
                'video' => [
                    'convidado' => ['ver'],
                    'usuario' => ['ler', 'novo', 'editar', 'status', 'ordem'],
                    'moderador' => ['ler', 'novo', 'editar', 'status', 'ordem', 'remover']
                ]
            ]
        ];
    }

}