<?php

namespace Modulo\Academia\Request;

use Core\Request\RequestApi;

class AcademiaRequestApi
{
    public function getApi()
    {
        return RequestApi::factory()->setEndpoint('/academia');
    }
}
