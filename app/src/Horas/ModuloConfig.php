<?php

namespace Modulo\Horas;

use Core\Inferface\ModuloConfigInterface;
use Modulo\Horas\Provider\HorasProvider;
use Modulo\Horas\Router\HorasRouter;

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
                'horas' => HorasRouter::class
            ],
            'provider'   => [
              HorasProvider::class => []
            ],
            'middleware' => [],
            'event'      => [],

        ];
    }
}
