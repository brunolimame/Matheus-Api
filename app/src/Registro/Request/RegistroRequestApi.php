<?php

namespace Modulo\Registro\Request;

use Core\Request\RequestApi;

class RegistroRequestApi
{
  public function getApi()
  {
    return RequestApi::factory()->setEndpoint('/registro');
  }
}
