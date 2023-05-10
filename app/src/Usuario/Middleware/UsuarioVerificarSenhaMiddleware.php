<?php

namespace Modulo\Usuario\Middleware;

use Core\Lib\ValidarCampos;
use Core\Lib\ValidacaoCollection;
use Core\Lib\ValidacaoParseRequest;
use Core\Inferface\MiddlewareInterface;
use Slim\Exception\HttpBadRequestException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Modulo\Usuario\Provider\UsuarioAclProvider;
use Symfony\Component\Validator\Constraints as Assert;
use Modulo\Usuario\Validator\UsuarioForcaSenhaValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UsuarioVerificarSenhaMiddleware implements MiddlewareInterface
{

    /** @var AclCheck */
    public $usuarioAcl;

    public function __construct(UsuarioAclProvider $usuarioAcl)
    {
        $this->usuarioAcl = $usuarioAcl->getAcl();
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        if ($request->getMethod() == 'POST') {

            $dadosRequisicao   = ValidacaoParseRequest::parse($request);
            $route = $request->getAttribute('__route__');
            $usuarioUuid = $route->getArgument('id');

            if ($this->usuarioAcl->jwtParse->tokenDecode['uuid'] != $usuarioUuid && !$this->usuarioAcl->isAdmin()) {
                throw new HttpBadRequestException($request, 'Acesso negado');
            }

            $listaRegras = [
                'password'    => [
                    new Assert\NotBlank(null, "O campo senha é obrigatório", false),
                    new Assert\Length(null, 6, 36, 'utf8', null, null, "O campo deve ter no minimo {{ limit }} cadacteres", "O campo deve ter no máximo {{ limit }} cadacteres"),
                    new Assert\Callback(function ($object, ExecutionContextInterface $context, $payload) {

                        if (!UsuarioForcaSenhaValidator::validarForcaSenha($object)) {
                            $context->buildViolation('Senha fraca. Na senha deve conter letras maiusculas, minuscula, numeros e no minimo 6 caracteres!')
                                ->addViolation();
                        }
                    })
                ],
                'password_confirmar'    => [
                    new Assert\NotBlank(null, "Confirmação de senha é obrigatória", false),
                    new Assert\Length(null, 6, 36, 'utf8', null, null, "O campo deve ter no minimo {{ limit }} cadacteres", "O campo deve ter no máximo {{ limit }} cadacteres"),
                ],
            ];

            if (!empty($dadosRequisicao['password'])) {
                array_push(
                    $listaRegras['password_confirmar'],
                    new Assert\EqualTo(
                        !empty($dadosRequisicao['password']) ? $dadosRequisicao['password'] : null,
                        null,
                        "A confirmação de senha está diferente da senha."
                    )
                );
            }

            if ($this->usuarioAcl->jwtParse->tokenDecode['uuid'] == $usuarioUuid || empty($usuarioUuid)) {
                $listaRegras['password_atual'] = [
                    new Assert\NotBlank(null, "Sua senha atual é obrigatória", false),
                    new Assert\Length(null, 5, 36, 'utf8', null, null, "O campo deve ter no minimo {{ limit }} cadacteres", "O campo deve ter no máximo {{ limit }} cadacteres"),
                ];
            }

            $regras = ValidacaoCollection::load($listaRegras);
            ValidarCampos::validar($request, $regras, $dadosRequisicao);
        }

        return $handler->handle($request);
    }
}
