<?php

namespace Core\Controller\Lib;

use Slim\Routing\RouteContext;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class ControllerRedirect
{
    
    public $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function gerarUrl(Request $request, string $rotaNome, array $parametros = [], array $queryParametros = []):string
    {
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        return $routeParser->urlFor($rotaNome, $parametros, $queryParametros);
    }

    public function irParaUrl($url,$codigo=302){
        return $this->responseFactory
            ->createResponse()
            ->withHeader('Location', $url)
            ->withStatus($codigo);
    }
}
