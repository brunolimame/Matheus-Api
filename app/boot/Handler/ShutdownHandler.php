<?php

namespace Boot\Handler;

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\ResponseEmitter;

class ShutdownHandler
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var HttpErrorHandler
     */
    private $errorHandler;

    /**
     * @var bool
     */
    private $displayErrorDetails;

    /**
     * ShutdownHandler constructor.
     *
     * @param Request           $request
     * @param HttpErrorHandler  $errorHandler
     * @param bool              $displayErrorDetails
     */
    public function __construct(Request $request, HttpErrorHandler $errorHandler, bool $displayErrorDetails) {
        $this->request = $request;
        $this->errorHandler = $errorHandler;
        $this->displayErrorDetails = $displayErrorDetails;
    }

    public function __invoke()
    {
        $error = error_get_last();
        if(!in_array($error['type'],[E_NOTICE])){
            if ($error) {
                $errorFile = $error['file'];
                $errorLine = $error['line'];
                $errorMessage = $error['message'];
                $errorType = $error['type'];
                $message = 'Um erro ao processar sua solicitação. Por favor, tente novamente mais tarde.';
    
                if ($this->displayErrorDetails) {
                    switch ($errorType) {
                        case E_USER_ERROR:
                            $message = "FATAL ERROR: {$errorMessage}. ";
                            $message .= " na linha {$errorLine} no arquivo {$errorFile}.";
                            break;
    
                        case E_USER_WARNING:
                            $message = "WARNING: {$errorMessage}";
                            break;
    
                        case E_USER_NOTICE:
                            $message = "NOTICE: {$errorMessage}";
                            break;
    
                        default:
                            $message = "ERROR: {$errorMessage}";
                            $message .= " na linha {$errorLine} no arquivo {$errorFile}.";
                            break;
                    }
                }
    
                $exception = new HttpInternalServerErrorException($this->request, $message);
                $response = $this->errorHandler->load($this->request, $exception, $this->displayErrorDetails, false, false,$this->errorHandler->container);
    
                if (ob_get_length()) {
                    ob_clean();
                }
    
                $responseEmitter = new ResponseEmitter();
                $responseEmitter->emit($response);
            }
        }
    }
}