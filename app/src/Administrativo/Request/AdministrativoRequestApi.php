<?php

namespace Modulo\Administrativo\Request;

use Core\Request\RequestApi;

class AdministrativoRequestApi
{
    public function getApi()
    {
        return RequestApi::factory()->setEndpoint('/administrativo');
    }
}
