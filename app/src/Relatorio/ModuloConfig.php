<?php

namespace Modulo\Relatorio;

use Modulo\Relatorio\Provider\RelatorioProvider;
use Core\Inferface\ModuloConfigInterface;
use Modulo\Relatorio\Router\RelatorioRouter;

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
                'relatorio' => RelatorioRouter::class
            ],
            'provider'   => [
                RelatorioProvider::class => []
            ],
            'middleware' => [],
            'event'      => [],
        ];
    }
}
