<?php

namespace Modulo\Academia;

use Modulo\Academia\Provider\AcademiaProvider;
use Core\Inferface\ModuloConfigInterface;
use Modulo\Academia\Router\AcademiaRouter;

class ModuloConfig implements ModuloConfigInterface
{
  static public function isEnable(): bool
  {
    return true;
  }

  static public function getConf(): array
  {
    return [
      'router' => [
        'academia' => AcademiaRouter::class
      ],
      'provider' => [
        AcademiaProvider::class => []
      ],
      'middleware' => [],
      'event' => [],
    ];
  }
}
