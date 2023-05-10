<?php

namespace Modulo\Versao;

use Modulo\Versao\Provider\VersaoProvider;
use Core\Inferface\ModuloConfigInterface;
use Modulo\Versao\Router\VersaoRouter;

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
                'versao' => VersaoRouter::class
            ],
            'provider'   => [
                VersaoProvider::class => []
            ],
            'middleware' => [],
            'event'      => [],
        ];
    }
}
