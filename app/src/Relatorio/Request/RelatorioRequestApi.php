<?php

namespace Modulo\Relatorio\Request;

use Core\Request\RequestApi;

class RelatorioRequestApi
{
    public function getApi()
    {
        return RequestApi::factory()->setEndpoint('/relatorio');
    }
}
