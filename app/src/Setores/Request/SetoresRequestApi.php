<?php

namespace Modulo\Setores\Request;

use Core\Request\RequestApi;

class SetoresRequestApi
{
    public function getApi()
    {
        return RequestApi::factory()->setEndpoint('/setores');
    }
}
