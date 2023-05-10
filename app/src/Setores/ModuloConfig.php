<?php

namespace Modulo\Setores;

use Modulo\Setores\Provider\SetoresProvider;
use Core\Inferface\ModuloConfigInterface;
use Modulo\Setores\Router\SetoresRouter;

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
                'setores' => SetoresRouter::class
            ],
            'provider'   => [
                SetoresProvider::class => []
            ],
            'middleware' => [],
            'event'      => [],
        ];
    }
}
