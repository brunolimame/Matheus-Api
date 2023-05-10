<?php

namespace Modulo\Usuario\Provider;

use Slim\Views\Twig;
use Core\Controller\Lib\ControllerView;

class UsuarioViewProvider
{
    /**@var Twig */
    public $twig;
    
    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    public function getView(){
        return new ControllerView($this->twig,'/Usuario/View/');
    }
}
