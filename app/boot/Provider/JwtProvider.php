<?php

namespace Boot\Provider;

use Boot\Provider\Jwt\JwtParse;
use Core\Inferface\ProviderInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Boot\Provider\Jwt\JwtRouter;
use Tuupola\Middleware\JwtAuthentication;
use Psr\Http\Message\ResponseInterface as Response;

class JwtProvider implements ProviderInterface
{
    const JWT_CONF = 'jwt.config';
    const JWT_HEADER = 'X-Token';
    const JWT_DECODE = '_token_decode';
    const JWT_HEADER_REFRESH = 'X-Token-Refresh';
    const JWT_KEY = '_token';
    const JWT_KEY_REFRESH = '_reftoken';

    static public function load(App &$app, \stdClass $args = null)
    {
        $args->secure          = !empty($args->secure) && is_bool($args->secure) ? $args->secure : false;
        $args->attribute       = self::JWT_KEY;
        $args->cookie          = $args->attribute;
        $args->header          = self::JWT_HEADER;
        $args->regexp          = !empty($args->regexp) ? $args->regexp : "/(.*)/";
        $args->path            = !empty($args->path) ? $args->path : '/json';
        $args->ignore          = !empty($args->ignore) ? $args->ignore : null;
        $args->life            = !empty($args->life) && is_int($args->life) ? $args->life : 3600;
        $args->life_public     = !empty($args->life_public) && is_int($args->life_public) ? $args->life_public : 36000;
        $args->life_refresh    = !empty($args->life_refresh) && is_int($args->life_refresh) ? $args->life_refresh : 36000;
        $args->secret          = !empty($args->secret) ? $args->secret : hash('sha256', $_SERVER['HTTP_HOST']);
        $args->secret_refresh  = !empty($args->secret_refresh) ? $args->secret_refresh : hash('sha256', $_SERVER['HTTP_HOST'] . $args->header);
        $args->algorithm       = !empty($args->algorithm) ? $args->algorithm : 'HS384';
        $container             = $app->getContainer();
        $container->set(self::JWT_CONF, $args);
        $app->group('/auth', JwtRouter::class);

        $args->before = function (Request $request, $arguments) use (&$container) {
            $container->set(JwtParse::class, new JwtParse($arguments["decoded"]));
        };

        $args->after = function (Response $response, $decoded) {
        };

        $args->error = function (Response $response, $args) {
            $payload = [
                'statusCode' => $response->getStatusCode(),
                'error'      => [
                    'type'        => 'ERROR',
                    'description' => $args['message'],
                ]
            ];
            $payload = json_encode($payload, JSON_PRETTY_PRINT);

            $response->getBody()->write($payload);
        };

        $app->add(new JwtAuthentication((array)$args));
    }
}
