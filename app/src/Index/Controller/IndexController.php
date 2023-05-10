<?php

namespace Modulo\Index\Controller;

use Core\Controller\BaseController;
use Psr\Container\ContainerInterface;
use Core\Controller\Lib\ControllerView;
use Modulo\Index\Provider\IndexViewProvider;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class IndexController extends BaseController
{
    /**@var ControllerView */
    public $view;

    public function __construct(ContainerInterface $container)
    {
        $this->view = $container->get(IndexViewProvider::class);
    }

    public function index(Request $request, Response $response): Response
    {
      return self::responseJson($response, [], 200);
    }
}
