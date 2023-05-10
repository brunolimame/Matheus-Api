<?php

namespace Modulo\Condominio\Request;

use Core\Request\RequestApi;

class CondominioRequestApi
{
    public function getApi()
    {
        return RequestApi::factory()->setEndpoint('/condominio');
    }
}
