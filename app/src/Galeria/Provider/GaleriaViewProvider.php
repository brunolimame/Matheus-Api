<?php

namespace Modulo\Galeria\Provider;

use Slim\Views\Twig;
use Core\Controller\Lib\ControllerView;

class GaleriaViewProvider extends ControllerView
{
    /**@var Twig */
    public $twig;
    
    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    public function getView(){
        return new ControllerView($this->twig,'/Galeria/View/');
    }
}
