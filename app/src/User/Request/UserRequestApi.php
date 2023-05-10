<?php

namespace Modulo\User\Request;

use Core\Request\RequestApi;

class UserRequestApi
{
    public function getApi()
    {
        return RequestApi::factory()->setEndpoint('/user');
    }
}
