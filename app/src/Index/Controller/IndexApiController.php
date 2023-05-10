<?php

namespace Modulo\Index\Controller;

use Core\Controller\BaseController;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class IndexApiController extends BaseController
{
    public function index(Request $request, Response $response)
    {
        $payload = [
            'statusCode' => 201,
            'data'       => [
                'nome' => "API: Index",
            ]
        ];

        return self::responseJson($response, $payload);
    }
}