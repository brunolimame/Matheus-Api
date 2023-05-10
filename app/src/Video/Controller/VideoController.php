<?php

namespace Modulo\Video\Controller;

use Core\Request\RequestApi;
use Core\Controller\BaseController;
use Core\Controller\Lib\ControllerView;
use Modulo\Video\Request\VideoRequestApi;
use Modulo\Video\Provider\VideoViewProvider;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class VideoController extends BaseController
{
    /**@var ControllerView */
    public $view;
    /**@var RequestApi */
    public $api;

    public function __construct(VideoViewProvider $videoViewProvider, VideoRequestApi $videoRequestApi)
    {
        $this->view = $videoViewProvider->getView();
        $this->api = $videoRequestApi->getApi();
    }


    public function index(Request $request, Response $response)
    {
        return $this->view->render($response, 'index');
    }
}
