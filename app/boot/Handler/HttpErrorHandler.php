<?php

namespace Boot\Handler;

use Core\BadRequestSerializadoException;
use DI\Container;
use Exception;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpNotImplementedException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Handlers\ErrorHandler;
use Slim\Interfaces\CallableResolverInterface;
use Throwable;

class HttpErrorHandler extends ErrorHandler
{
    public const BAD_REQUEST = 'BAD_REQUEST';
    public const INSUFFICIENT_PRIVILEGES = 'INSUFFICIENT_PRIVILEGES';
    public const NOT_ALLOWED = 'NOT_ALLOWED';
    public const NOT_IMPLEMENTED = 'NOT_IMPLEMENTED';
    public const RESOURCE_NOT_FOUND = 'RESOURCE_NOT_FOUND';
    public const SERVER_ERROR = 'SERVER_ERROR';
    public const UNAUTHENTICATED = 'UNAUTHENTICATED';
    public $container;

    /**
     * @param CallableResolverInterface $callableResolver
     * @param ResponseFactoryInterface $responseFactory
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        CallableResolverInterface $callableResolver,
        ResponseFactoryInterface  $responseFactory,
        ?LoggerInterface          $logger = null,
        Container                 $container
    ) {
        $this->container = $container;
        parent::__construct($callableResolver, $responseFactory, $logger);
    }

    /**
     * Invoke error handler
     *
     * @param ServerRequestInterface $request The most recent Request object
     * @param Throwable $exception The caught Exception object
     * @param bool $displayErrorDetails Whether or not to display the error details
     * @param bool $logErrors Whether or not to log errors
     * @param bool $logErrorDetails Whether or not to log error details
     * @param container $container
     *
     * @return ResponseInterface
     */
    public function load(
        ServerRequestInterface $request,
        Throwable              $exception,
        bool                   $displayErrorDetails,
        bool                   $logErrors,
        bool                   $logErrorDetails,
        Container              $container
    ):ResponseInterface {
        $this->displayErrorDetails = $displayErrorDetails;
        $this->logErrors           = $logErrors;
        $this->logErrorDetails     = $logErrorDetails;
        $this->request             = $request;
        $this->container           = $container;
        $this->exception           = $exception;
        $this->method              = $request->getMethod();
        $this->statusCode          = $this->determineStatusCode();
        if ($this->contentType === null) {
            $this->contentType = $this->determineContentType($request);
        }

        if ($logErrors) {
            $this->writeToErrorLog();
        }

        return $this->respond();
    }

    protected function respond():ResponseInterface
    {
        $exception  = $this->exception;
        $statusCode = $exception->getCode() == 0 ? 500 : $exception->getCode();
        $type       = self::SERVER_ERROR;

        $description = 'Ocorreu um erro interno ao processar sua solicitação.';

        if ($exception instanceof HttpException) {
            $statusCode  = $exception->getCode();
            $description = $exception->getMessage();

            if ($exception instanceof HttpNotFoundException) {
                $type = self::RESOURCE_NOT_FOUND;
            } else if ($exception instanceof HttpMethodNotAllowedException) {
                $type = self::NOT_ALLOWED;
            } else if ($exception instanceof HttpUnauthorizedException) {
                $type = self::UNAUTHENTICATED;
            } else if ($exception instanceof HttpForbiddenException) {
                $type = self::UNAUTHENTICATED;
            } else if ($exception instanceof HttpBadRequestException) {
                $type = self::BAD_REQUEST;
            } else if ($exception instanceof HttpNotImplementedException) {
                $type = self::NOT_IMPLEMENTED;
            }
        }

        if (
            !($exception instanceof HttpException)
            && ($exception instanceof Exception || $exception instanceof Throwable)
            && $this->displayErrorDetails
        ) {
            $description = $exception->getMessage();
        }
        if ($exception instanceof BadRequestSerializadoException) {
            $description = unserialize($description);
        }

        $error = [
            'statusCode' => $statusCode,
            'error'      => [
                'type'        => $type,
                'description' => $description,
            ],
        ];

        $response = $this->responseFactory->createResponse($statusCode);
//        preg_match('/\/json?(.*)/', $this->request->getUri()->getPath(), $output_json_array);
//        preg_match('/\/auth?(.*)/', $this->request->getUri()->getPath(), $output_auth_array);
//
//        if (!empty($output_json_array) || !empty($output_auth_array)) {
            $payload = json_encode($error, JSON_PRETTY_PRINT);
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withStatus($statusCode);
//        }
//        return $this->container->get('view')->render($response, '/View/error.html.twig', [
//            'erro' => $error
//        ]);
    }
}