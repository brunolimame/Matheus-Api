<?php

namespace Core\Middleware;

use Core\Lib\ValidarCampos;
use Core\Lib\GoogleRecaptcha;
use Core\Lib\ValidacaoCollection;
use Core\Lib\ValidacaoParseRequest;
use Psr\Container\ContainerInterface;
use Core\Inferface\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ValidarGoogleRecaptchaMiddleware implements MiddlewareInterface
{

    public $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        if ($request->getMethod() == 'POST') {
            $googleConfig = $this->container->get('_google');
            $dadosRequisicao   = ValidacaoParseRequest::parse($request);
            $regras = ValidacaoCollection::load([
                'g-recaptcha-response'    => [
                    new Assert\NotBlank(null, "O campo g-recaptcha-response é obrigatório", false),
                    new Assert\Callback(function($object, ExecutionContextInterface $context, $payload) use($googleConfig){
                        if(!GoogleRecaptcha::validReCaptcha($googleConfig['recaptcha']['secret'],$object)){
                            $context->buildViolation('Recaptcha incorreto. Resolta o recaptcha corretamente.')
                            ->addViolation();
                        }
                    })
                ]
            ]);
            ValidarCampos::validar($request, $regras, $dadosRequisicao);

        }

        return $handler->handle($request);

    }
}