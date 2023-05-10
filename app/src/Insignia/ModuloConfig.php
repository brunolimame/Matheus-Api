<?php

namespace Modulo\Insignia;

use Modulo\Insignia\Provider\InsigniaProvider;
use Core\Inferface\ModuloConfigInterface;
use Modulo\Insignia\Router\InsigniaRouter;

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
                'insignia' => InsigniaRouter::class
            ],
            'provider'   => [
                InsigniaProvider::class => []
            ],
            'middleware' => [],
            'event'      => [],
        ];
    }
}
