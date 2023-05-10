<?php

namespace Modulo\Registro;

use Modulo\Registro\Provider\RegistroProvider;
use Core\Inferface\ModuloConfigInterface;
use Modulo\Registro\Router\RegistroRouter;

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
        'registro' => RegistroRouter::class
      ],
      'provider' => [
        RegistroProvider::class => []
      ],
      'middleware' => [],
      'event' => [],
    ];
  }
}
