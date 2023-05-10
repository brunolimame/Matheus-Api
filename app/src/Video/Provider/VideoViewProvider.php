<?php

namespace Modulo\Video\Provider;

use Slim\Views\Twig;
use Core\Controller\Lib\ControllerView;

class VideoViewProvider extends ControllerView
{
    
    /**@var Twig */
    public $twig;
        
    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    public function getView(){
        return new ControllerView($this->twig,'/Video/View/');
    }
}
