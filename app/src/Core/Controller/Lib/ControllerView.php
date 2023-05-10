<?php

namespace Core\Controller\Lib;

use Slim\Routing\RouteContext;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class ControllerView
{
    /** @var Twig */
    public $twig;
    public $pasta;

    public function __construct(Twig $twig, $pasta)
    {
        $this->twig = $twig;
        $this->pasta = $pasta;
    }


    public function render($response, $tema, $dados = [])
    {
        return $this->twig->render($response, "{$this->pasta}{$tema}.html.twig", [
            'dados' => $dados
        ]);
    }

    public function renderHtml($tema, $dados = [])
    {
        return $this->twig->fetch("{$this->pasta}{$tema}.html.twig", $dados);
    }
}
