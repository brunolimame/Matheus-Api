<?php

namespace Modulo\Planejamento\Request;

use Core\Request\RequestApi;

class PlanejamentoRequestApi
{
    public function getApi()
    {
        return RequestApi::factory()->setEndpoint('/planejamento');
    }
}
