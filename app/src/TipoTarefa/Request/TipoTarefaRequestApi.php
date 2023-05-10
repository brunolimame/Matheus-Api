<?php

namespace Modulo\TipoTarefa\Request;

use Core\Request\RequestApi;

class TipoTarefaRequestApi
{
    public function getApi()
    {
        return RequestApi::factory()->setEndpoint('/tipotarefa');
    }
}
