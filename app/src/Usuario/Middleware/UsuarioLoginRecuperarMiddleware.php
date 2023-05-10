<?php

namespace Modulo\Usuario\Middleware;

use Core\Lib\ValidarCampos;
use Core\Lib\ValidacaoCollection;
use Core\Lib\ValidacaoParseRequest;
use Psr\Container\ContainerInterface;
use Core\Inferface\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Modulo\Usuario\Validator\UsuarioForcaSenhaValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UsuarioLoginRecuperarMiddleware implements MiddlewareInterface
{

    public $google;

    public function __construct(ContainerInterface $container)
    {
        $this->google = $container->get('_google');
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        if ($request->getMethod() == 'POST') {
            $queryRequest = $request->getQueryParams();
            $dadosRequisicao = ValidacaoParseRequest::parse($request);
            if (empty($queryRequest['chave'])) {
                $regras = ValidacaoCollection::load([
                    '_username'    => [
                        new Assert\NotBlank(null, "Informe o nome de usuário/e-mail", false),
                        new Assert\Length(null, 5, 100, 'utf8', null, null, "O campo deve ter no minimo {{ limit }} cadacteres", "O campo deve ter no máximo {{ limit }} cadacteres")
                    ]
                ]);
            } else {
                $dadosRequisicao = array_merge($dadosRequisicao, $queryRequest);
                $listaRegras = [
                    'chave'    => [
                        new Assert\NotBlank(null, "Chave não informada", false)
                    ],
                    '_password'    => [
                        new Assert\NotBlank(null, "O campo senha é obrigatório", false),
                        new Assert\Length(null, 6, 36, 'utf8', null, null, "O campo deve ter no minimo {{ limit }} cadacteres", "O campo deve ter no máximo {{ limit }} cadacteres"),
                        new Assert\Callback(function ($object, ExecutionContextInterface $context, $payload) {
                            if (!UsuarioForcaSenhaValidator::validarForcaSenha($object)) {
                                $context->buildViolation('Senha fraca. Na senha deve conter letras maiusculas, minuscula, numeros e no minimo 6 caracteres!')
                                    ->addViolation();
                            }
                        })
                    ],
                    '_password_confirmar'    => [
                        new Assert\NotBlank(null, "Confirmação de senha é obrigatória", false),
                        new Assert\Length(null, 6, 36, 'utf8', null, null, "O campo deve ter no minimo {{ limit }} cadacteres", "O campo deve ter no máximo {{ limit }} cadacteres"),
                    ],
                ];

                if (!empty($dadosRequisicao['_password'])) {
                    array_push(
                        $listaRegras['_password_confirmar'],
                        new Assert\EqualTo(
                            !empty($dadosRequisicao['_password']) ? $dadosRequisicao['_password'] : null,
                            null,
                            "A confirmação de senha está diferente da senha."
                        )
                    );
                }
                $regras = ValidacaoCollection::load($listaRegras);
            }

            ValidarCampos::validar($request, $regras, $dadosRequisicao);
        }

        return $handler->handle($request);
    }
}
