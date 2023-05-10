<?php

namespace Modulo\Galeria\Request;

use Core\Request\RequestApi;

class GaleriaFotoRequestApi
{
    public function getApi()
    {
        return RequestApi::factory()->setEndpoint('/json/galeria/foto');
    }
}
