<?php

namespace Modulo\Passo\Request;

use Core\Request\RequestApi;

class PassoRequestApi
{
    public function getApi()
    {
        return RequestApi::factory()->setEndpoint('/passo');
    }
}
