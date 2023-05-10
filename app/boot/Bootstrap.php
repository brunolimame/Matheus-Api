<?php
session_start();
require_once __DIR__ . './../../vendor/autoload.php';

use Boot\{BootConfig, BootLoad, Middleware\JsonBodyParserMiddleware, Middleware\ParseRequestToValidationMiddleware};
use DI\Bridge\Slim\Bridge as SlimBridge;
use DI\Container;
use Middlewares\TrailingSlash;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest as Request;
use Boot\Provider\JwtProvider;

// Create Container using PHP-DI
$container = new Container();
// Set container to create App with on AppFactory
$configApp = BootLoad::loadConfigApp();

if ($configApp['debug']) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL & ~E_NOTICE);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

if (PHP_MAJOR_VERSION >= 7) {
    set_error_handler(function ($errno, $errstr) {
        return strpos($errstr, 'Declaration of') === 0;
    }, E_WARNING);
}

$container->set('debug', $configApp['debug']);
$container->set('_google', $configApp['google']);
$container->set('_api', $configApp['api']);

$app = SlimBridge::create($container);

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        if(in_array($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'],['GET', 'POST', 'PUT','DELETE','OPTIONS'])) {
            $joinAllowHeader = implode(', ', ["X-Requested-With","Content-Type","Accept","Origin","Authorization",JwtProvider::JWT_HEADER,JwtProvider::JWT_HEADER_REFRESH]);
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
            header("Access-Control-Allow-Headers: $joinAllowHeader");
        }
    }
    exit;
}

(new BootLoad($app))
    ->loadCors()
    ->loadProviders(BootConfig::listProvider($configApp['debug']))
    ->loadModulos();

$app->get('/json', function (Request $request, Response $response, $args = []) {
    throw new \Exception("Rota inválida");
});


$app->add((new TrailingSlash())->redirect())
    ->add(JsonBodyParserMiddleware::class);

$app->run();