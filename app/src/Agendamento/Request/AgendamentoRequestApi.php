<?php

namespace Modulo\Agendamento\Request;

use Core\Request\RequestApi;

class AgendamentoRequestApi
{
    public function getApi()
    {
        return RequestApi::factory()->setEndpoint('/agendamento');
    }
}
