<?php

namespace Modulo\Tarefa\Request;

use Core\Request\RequestApi;

class TarefaRequestApi
{
    public function getApi()
    {
        return RequestApi::factory()->setEndpoint('/tarefa');
    }
}
