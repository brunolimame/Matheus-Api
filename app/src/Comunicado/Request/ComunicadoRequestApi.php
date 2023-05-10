<?php

namespace Modulo\Comunicado\Request;

use Core\Request\RequestApi;

class ComunicadoRequestApi
{
    public function getApi()
    {
        return RequestApi::factory()->setEndpoint('/comunicado');
    }
}
