<?php

namespace Modulo\Index;

use Core\Inferface\ModuloConfigInterface;
use Modulo\Index\Provider\IndexProvider;
use Modulo\Index\Router\IndexApiRouter;
use Modulo\Index\Router\IndexRouter;

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
                '' => IndexRouter::class,
                'json'  => [
                    'index' => IndexApiRouter::class

                ]
            ],
            'provider'   => [
                IndexProvider::class => []
            ],
            'middleware' => [],
            'event'      => [],
            'acl' => [
                'index' => [
                    'convidado' => ['ler'],
                    'usuario' => ['ler'],
                    'moderador' => ['ler']
                ]
            ]
        ];
    }
}
