<?php

namespace Boot\Provider;

use Boot\Handler\HttpErrorHandler;
use Boot\Handler\ShutdownHandler;
use Core\Inferface\ProviderInterface;
use Slim\App;
use Slim\Factory\ServerRequestCreatorFactory;

class HandlerErroProvider implements ProviderInterface
{
    static public function load(App &$app, \stdClass $args = null)
    {

        $args->displayError    = !is_bool($args->displayErros) || $args->displayErros;
        $args->logErros        = is_bool($args->logErros) && $args->logErros;
        $args->logErrosDetails = is_bool($args->logErrosDetails) && $args->logErrosDetails;

        $callableResolver = $app->getCallableResolver();
        $responseFactory  = $app->getResponseFactory();

        $serverRequestCreator = ServerRequestCreatorFactory::create();
        $request              = $serverRequestCreator->createServerRequestFromGlobals();
        $errorHandler    = new HttpErrorHandler($callableResolver, $responseFactory, null,$app->getContainer());
        $shutdownHandler = new ShutdownHandler($request, $errorHandler, $args->displayError);
        register_shutdown_function($shutdownHandler,['container'=>$app->getContainer()]);

        // Add Routing Middleware
        $app->addRoutingMiddleware();

        // Add Error Handling Middleware
        $errorMiddleware = $app->addErrorMiddleware($args->displayError, $args->logErros, $args->logErrosDetails);
        $errorMiddleware->setDefaultErrorHandler($errorHandler);
    }
}