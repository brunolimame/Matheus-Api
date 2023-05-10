<?php

namespace Core\Inferface;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

interface ControllerInterface
{

    static public function responseJson(Response $response, $data = [], $codigo = 201);
}