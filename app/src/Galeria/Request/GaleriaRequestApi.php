<?php

namespace Modulo\Galeria\Request;

use Core\Request\RequestApi;

class GaleriaRequestApi
{
    public function getApi()
    {
        return RequestApi::factory()->setEndpoint('/json/galeria');
    }
}
