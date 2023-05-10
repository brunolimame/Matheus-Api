<?php

namespace Modulo\Clientecontato\Request;

use Core\Request\RequestApi;

class ClientecontatoRequestApi
{
    public function getApi()
    {
        return RequestApi::factory()->setEndpoint('/clientecontato');
    }
}
