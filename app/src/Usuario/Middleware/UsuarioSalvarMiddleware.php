<?php

namespace Modulo\Usuario\Middleware;

use Core\Entity\Value\EntityValueSlug;
use Core\Lib\ValidarCampos;
use Core\Lib\ValidacaoCollection;
use Core\Lib\ValidacaoParseRequest;
use Core\Inferface\MiddlewareInterface;
use Modulo\Usuario\Entity\UsuarioEntity;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Modulo\Usuario\Repository\UsuarioRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Modulo\Usuario\Validator\UsuarioForcaSenhaValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UsuarioSalvarMiddleware implements MiddlewareInterface
{

    /** @var UsuarioRepository */
    public $usuarioRepository;

    public function __construct(UsuarioRepository $usuarioRepository)
    {
        $this->usuarioRepository = $usuarioRepository;
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        if ($request->getMethod() == 'POST') {

            $dadosRequisicao   = ValidacaoParseRequest::parse($request);
            $parametrosArquivo = UsuarioEntity::getParametrosParaArquivo();
            $route = $request->getAttribute('__route__');
            $usuarioUuid = $route->getArgument('id');

            $listaRegras = [
                'nome'    => [
                    new Assert\NotBlank(null, "O campo nome é obrigatório", false),
                    new Assert\Length(null, 5, 255, 'utf8', null, null, "O campo deve ter no minimo {{ limit }} cadacteres", "O campo deve ter no máximo {{ limit }} cadacteres")
                ],
                'username'    => [
                    new Assert\NotBlank(null, "O campo username é obrigatório", false),
                    new Assert\Length(null, 5, 255, 'utf8', null, null, "O campo deve ter no minimo {{ limit }} cadacteres", "O campo deve ter no máximo {{ limit }} cadacteres"),
                    new Assert\Callback(function ($object, ExecutionContextInterface $context, $payload) use ($usuarioUuid) {
                        $prepararUsername = (new EntityValueSlug())->add($object);
                        $entityUsuario = $this->usuarioRepository->verificarUsername($prepararUsername->value(), $usuarioUuid);
                        if (!empty($entityUsuario)) {
                            $context->buildViolation('Nome de usuário já cadastrado!')
                                ->addViolation();
                        }
                    })
                ],
                'email'    => [
                    new Assert\NotBlank(null, "O campo eamil é obrigatório", false),
                    new Assert\Length(null, 5, 255, 'utf8', null, null, "O campo deve ter no minimo {{ limit }} cadacteres", "O campo deve ter no máximo {{ limit }} cadacteres"),
                    new Assert\Callback(function ($object, ExecutionContextInterface $context, $payload) use ($usuarioUuid) {

                        $entityUsuario = $this->usuarioRepository->verificarEmail(strtolower($object), $usuarioUuid);
                        if (!empty($entityUsuario)) {
                            $context->buildViolation('E-mail já cadastrado!')
                                ->addViolation();
                        }
                    })
                ],
                'nivel'    => [
                    new Assert\NotBlank(null, "O nível do usuário deve ser selecionado", false),
                    new Assert\Choice(array_keys(UsuarioEntity::getListaNiveis()), null, false, true, 1, null, "Nivel de usuário inválido.")
                ],
                'foto'   => new Assert\Optional([
                    new Assert\NotBlank(null, "Arquivo não enviado", false),
                    new Assert\Image([
                        "maxSize"          => $parametrosArquivo->getTamanho() . "M",
                        "maxSizeMessage"   => "Arquivo muito Grande. Envie arquivos com até {{ limit }}MB.",
                        "minWidth"         => $parametrosArquivo->getMedidas()[1][0],
                        "minWidthMessage"  => "A imagem é muito pequena ({{ width }}px). Envie imagens com no minimo {{ min_width }}px.",
                        "mimeTypes"        => $parametrosArquivo->getTipo(),
                        "mimeTypesMessage" => "Arquivo inválido.",
                    ])
                ])
            ];


            if (!empty($courseId)) {
                $listaRegras['password'] = [
                    new Assert\NotBlank(null, "O campo passowrd é obrigatório", false),
                    new Assert\Length(null, 6, 36, 'utf8', null, null, "O campo deve ter no minimo {{ limit }} cadacteres", "O campo deve ter no máximo {{ limit }} cadacteres"),
                    new Assert\Callback(function ($object, ExecutionContextInterface $context, $payload) {

                        if (!UsuarioForcaSenhaValidator::validarForcaSenha($object)) {
                            $context->buildViolation('Senha fraca. Na senha deve conter letras maiusculas, minuscula, numeros e no minimo 6 caracteres!')
                                ->addViolation();
                        }
                    })
                ];
                $listaRegras['password_confirmar'] = [
                    new Assert\NotBlank(null, "Confirmação de senha é obrigatória", false),
                    new Assert\Length(null, 6, 36, 'utf8', null, null, "O campo deve ter no minimo {{ limit }} cadacteres", "O campo deve ter no máximo {{ limit }} cadacteres"),

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
            }

            $regras = ValidacaoCollection::load($listaRegras);
            ValidarCampos::validar($request, $regras, $dadosRequisicao);
        }

        return $handler->handle($request);
    }
}
