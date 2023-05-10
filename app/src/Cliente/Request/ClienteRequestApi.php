<?php

namespace Modulo\Cliente\Request;

use Core\Request\RequestApi;

class ClienteRequestApi
{
    public function getApi()
    {
        return RequestApi::factory()->setEndpoint('/cliente');
    }
}
