<?php

namespace Modulo\Post\Request;

use Core\Request\RequestApi;

class PostRequestApi
{
    public function getApi()
    {
        return RequestApi::factory()->setEndpoint('/post');
    }
}
