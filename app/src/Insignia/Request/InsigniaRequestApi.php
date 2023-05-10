<?php

namespace Modulo\Insignia\Request;

use Core\Request\RequestApi;

class InsigniaRequestApi
{
    public function getApi()
    {
        return RequestApi::factory()->setEndpoint('/insignia');
    }
}
