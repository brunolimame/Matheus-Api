<?php

namespace Modulo\Video\Request;

use Core\Request\RequestApi;

class VideoRequestApi
{
    public function getApi()
    {
        return RequestApi::factory()->setEndpoint('/json/video');
    }
}
