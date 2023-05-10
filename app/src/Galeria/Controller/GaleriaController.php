<?php

namespace Modulo\Galeria\Controller;

use Core\Request\RequestApi;
use Core\Controller\BaseController;
use Psr\Container\ContainerInterface;
use Core\Controller\Lib\ControllerView;
use Modulo\Galeria\Request\GaleriaRequestApi;
use Modulo\Galeria\Provider\GaleriaViewProvider;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GaleriaController extends BaseController
{

    /**@var ControllerView */
    public $view;
    /**@var RequestApi */
    public $api;

    public function __construct(GaleriaViewProvider $galeriaViewProvider, GaleriaRequestApi $galeriaRequestApi)
    {
        $this->view = $galeriaViewProvider->getView();
        $this->api = $galeriaRequestApi->getApi();
    }

    public function index(Request $request, Response $response)
    {

        var_dump($this->api->exec());
        die;
        $response->getBody()->write("Galeria");
        return $response;
    }
}
