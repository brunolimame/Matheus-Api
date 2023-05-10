<?php

namespace Core\Controller;

use Core\Inferface\ControllerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class BaseController implements ControllerInterface
{
    /**
     * @param Response $response
     * @param array $data
     * @param int $codigo
     * @return Response
     */
    static public function responseJson(Response $response, $data = [], $codigo = 201)
    {
        $payload = json_encode([
            'statusCode' => $codigo,
            'data'       => $data
        ]);

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($codigo);
    }

    /**
     * @param Response $response
     * @param $data
     * @param int $codigo
     * @return Response
     */
    static public function responseJsonNovo(Response $response, $data = [], int $codigo = 200): Response
    {
        $payload = json_encode($data);

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($codigo);
    }
    
    public function factoryRequestQueryParams(Request $request,$addParams=[]){
        $params = $request->getQueryParams();
        if(is_array($addParams) && !empty($addParams)){
            array_walk($addParams,function($value,$key) use(&$params){
                if(empty($params[$key])){
                    $params[$key] = $value;
                }
            });
        }
        return $params;
    }
}