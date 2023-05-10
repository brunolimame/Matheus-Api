<?php

namespace Modulo\Versao\Request;

use Core\Request\RequestApi;

class VersaoRequestApi
{
    public function getApi()
    {
        return RequestApi::factory()->setEndpoint('/versao');
    }
}
