<?php

namespace Modulo\Usuario\Controller;

use Core\Repository\FactoryFindBy;
use Core\Controller\BaseController;
use Core\Entity\Value\EntityValueSlug;
use Core\Lib\Acl\AclCheck;
use Modulo\Usuario\Entity\UsuarioEntity;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpBadRequestException;
use Modulo\Usuario\Provider\UsuarioAclProvider;
use Modulo\Usuario\Repository\UsuarioRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Modulo\Usuario\Event\UsuarioNotificarContaChaveEvent;
use Modulo\Usuario\Event\UsuarioNotificarContaChaveAlterarSenhaEvent;

class UsuarioApiController extends BaseController
{

    /**@var UsuarioRepository */
    public $usuarioRepository;
    /**@var AclCheck*/
    public $acl;

    const PARAMS = [
        'select' => 'id,uuid,nome,foto,username,email,nivel,criado,alterado'
    ];

    public function __construct(UsuarioRepository $usuarioRepository, UsuarioAclProvider $usuarioAclProvider)
    {
        $this->usuarioRepository = $usuarioRepository;
        $this->acl = $usuarioAclProvider->getAcl();
    }

    public function index(Request $request, Response $response)
    {
        $definirStatus = $this->acl->isAllowed('ler-todos') ? null : 1;
        $uuid = $request->getQueryParams()['uuid'];
        if (!is_null($uuid)) {
            if ($this->acl->isAllowed('ler-todos')) {
                $itens = $this->usuarioRepository->getRepository()->find($uuid);
            } else {
                if ($this->acl->jwtParse->tokenDecode->uuid != $uuid) {
                    throw new HttpNotFoundException($request, "Acesso negado");
                }
                $itens = FactoryFindBy::factory($this->usuarioRepository->getRepository(), ['where' => "uuid#{$uuid}"], $definirStatus)->getItem();
            }

            if (empty($itens)) {
                throw new HttpNotFoundException($request, "Registro inválido");
            }
        } else {
            $requestParams = $this->factoryRequestQueryParams($request, self::PARAMS);
            $itens = FactoryFindBy::factory($this->usuarioRepository->getRepository(), $requestParams, $definirStatus);
        }

        return self::responseJson($response, $itens->toApi());
    }

    public function temAcesso(Request $request, Response $response)
    {
        $jwtParse = $this->acl->jwtParse;
        $dadosRequisicao = $request->getQueryParams();
        $autorizacao = $this->acl->isAllowed($jwtParse->nivel, $dadosRequisicao['recurso'], $dadosRequisicao['funcao']);
        return self::responseJson($response, ['acesso' => $autorizacao]);
    }


    public function listaNiveis(Request $request, Response $response)
    {
        return self::responseJson($response, UsuarioEntity::getListaNiveis());
    }

    public function verificarUsername(Request $request, Response $response)
    {
        $username = $request->getQueryParams()['username'];

        $usernameJaCadastrado = $this->usuarioRepository->verificarUsername($username);
        return self::responseJson($response, ['username' => !empty($usernameJaCadastrado)]);
    }

    public function verificarEmail(Request $request, Response $response)
    {
        $email = $request->getQueryParams()['email'];

        $emailJaCadastrado = $this->usuarioRepository->verificarEmail($email);
        return self::responseJson($response, ['email' => !empty($emailJaCadastrado)]);
    }

    public function salvar(Request $request, Response $response)
    {
        $uuid = $request->getQueryParams()['uuid'];
        if (empty($uuid)) {
            if (!$this->acl->isAllowed('novo')) {
                throw new HttpBadRequestException($request, 'Acesso negado');
            }
            $entity = $this->usuarioRepository->getRepository()::getEntity();
        } else {
            if (!$this->acl->isAllowed('editar')) {
                throw new HttpBadRequestException($request, 'Acesso negado');
            }

            $entity = $this->usuarioRepository->getRepository()->find($uuid);
            if (empty($entity)) {
                throw new HttpNotFoundException($request, "Registro inválido;");
            }

            if ($entity->nivel->value() == 'sadmin' && !$this->acl->isAllowed('sadmin')) {
                throw new HttpBadRequestException($request, 'Acesso negado');
            }
        }

        /**@var UsuarioEntity $entity */
        $entity->hydrator($request->getParsedBody());
        $limparUsername = (new EntityValueSlug())->add($entity->username->value())->value();
        $entity->username->set($limparUsername);
        $entity->email->set(strtolower($entity->email->value()));

        if ($entity->id->value() == 0) {
            $entity->status->set(1);
        }

        if ($entity->id->value() > 0) {
            $registroEditado = $this->usuarioRepository->getRepository()->update(['uuid' => $entity->uuid->value()], $entity->toSave('editar'));
            if ($registroEditado) {
                return self::responseJson($response, $entity->toApi());
            } else {
                throw new \Exception("Não foi possível editar o registro.");
            }
        } else {

            $novoRegistroId = $this->usuarioRepository->getRepository()->insert($entity->toSave('novo'));
            if ($novoRegistroId) {
                $novosDados = $this->usuarioRepository->getRepository()->find($novoRegistroId, 'id');
                return self::responseJson($response, $novosDados->toApi());
            } else {
                throw new \Exception("Não foi possível criar o registro.");
            }
        }
    }

    public function senha(Request $request, Response $response)
    {
        $uuid = $request->getQueryParams()['uuid'];
        $idUsuario = !empty($uuid) ? $uuid : $this->acl->jwtParse->tokenDecode['uuid'];
        if (empty($idUsuario)) {
            throw new \Exception("Usuário inválido.");
        }
        $requestParametros = $request->getParsedBody();
        $novaSenhaTexto = $requestParametros['password'];
        $senhaAtual = !empty($requestParametros['password_atual']) ? $requestParametros['password_atual'] : null;

        /** @var UsuarioEntity $usuarioEntity */
        $usuarioEntity = $this->usuarioRepository->getRepository()->findBy(['uuid' => $idUsuario])->getItem();

        if (empty($usuarioEntity)) {
            throw new \Exception("Usuário inválido.");
        }

        if ($usuarioEntity->nivel->value() == 'sadmin' && !$this->acl->isAllowed('senha-sadmin')) {
            throw new \Exception("Acesso negado.");
        }

        if (!empty($senhaAtual)) {
            if (!$usuarioEntity->isValidPass(
                $senhaAtual,
                $usuarioEntity->salt->value(),
                $usuarioEntity->password->value()
            )) {
                throw new \Exception("Senha atual inválida.");
            }
        }

        if ($usuarioEntity->isValidPass(
            $novaSenhaTexto,
            $usuarioEntity->salt->value(),
            $usuarioEntity->password->value()
        )) {
            throw new \Exception("A nova senha deve ser diferente da atual.");
        }

        $novaSenhaHash = $usuarioEntity->encodePass($novaSenhaTexto, $usuarioEntity->salt->value());
        $usuarioEntity->password->set($novaSenhaHash);

        $uuidUsuarioLogado =  $this->acl->jwtParse->tokenDecode['uuid'];
        $registroEditado = $this->usuarioRepository->getRepository()->update(['uuid' => $usuarioEntity->uuid->value()], $usuarioEntity->toSave('nova_senha', $uuidUsuarioLogado));

        if ($registroEditado) {
            return self::responseJson($response, $usuarioEntity->toApi());
        } else {
            throw new \Exception("Não foi possível alterar a senha.");
        }
    }

    public function status($id, $status, Request $request, Response $response)
    {
        if (!$this->acl->isAllowed('status')) {
            throw new HttpBadRequestException($request, 'Acesso negado');
        }
        $uuid = $request->getQueryParams()['uuid'];
        $entity = $this->usuarioRepository->getRepository()->find($uuid);
        if (empty($entity)) {
            throw new HttpNotFoundException($request, "Registro inválido;");
        }

        if ($entity->nivel->value() == 'sadmin' && !$this->acl->isAllowed('sadmin')) {
            throw new HttpBadRequestException($request, 'Acesso negado');
        }

        $novoStatus = (int)($status == 'a');

        $entity->status->set($novoStatus);

        $registroEditado = $this->usuarioRepository->getRepository()->update(['uuid' => $entity->uuid->value()], $entity->toSave('status'));
        if ($registroEditado) {
            return self::responseJson($response, $entity->toApi());
        } else {
            throw new \Exception("Não foi possível alteara o status.");
        }
    }


    public function delete(Request $request, Response $response)
    {
        if (!$this->acl->isAllowed('delete')) {
            throw new HttpBadRequestException($request, 'Acesso negado');
        }
        $uuid = $request->getQueryParams()['uuid'];
        $entity = $this->usuarioRepository->getRepository()->find($uuid);
        if (empty($entity)) {
            throw new HttpNotFoundException($request, "Registro inválido;");
        }

        if ($entity->nivel->value() == 'sadmin' && !$this->acl->isAllowed('sadmin')) {
            throw new HttpBadRequestException($request, 'Acesso negado');
        }

        $apagar = $this->usuarioRepository->getRepository()->delete(['uuid' => $entity->uuid->value()]);

        if (!$apagar) {
            throw new \HttpException($request, "Não foi possível apagar o registro.");
        }

        return self::responseJson($response, ['removido' => $entity->uuid->value()]);
    }

    public function recuperar(
        Request $request,
        Response $response,
        UsuarioNotificarContaChaveEvent $usuarioNotificarContaChaveEvent,
        UsuarioNotificarContaChaveAlterarSenhaEvent $usuarioNotificarContaChaveAlterarSenhaEvent
     ) {

        $dataReq = $request->getParsedBody();
        $novaSenhaTexto = $dataReq['_password'];
        $chave = $request->getQueryParams()['chave'];

        if (!empty($chave)) {
            $usuarioEntity = $this->usuarioRepository->getRepository()->findBy(['chave'=>$chave,'status'=>'1'])->getItem();

            if($usuarioEntity){
                $usuarioNotificarContaChaveAlterarSenhaEvent->setUsuario(clone($usuarioEntity));
                $novaSenhaHash = $usuarioEntity->encodePass($novaSenhaTexto, $usuarioEntity->salt->value());
                $usuarioEntity->chave->set(null);
                $usuarioEntity->password->set($novaSenhaHash);
                
                $novaSenhaSalva = $this->usuarioRepository->getRepository()->update(['uuid' => $usuarioEntity->uuid->value()], $usuarioEntity->toSave('recuperar'));
                if ($novaSenhaSalva) {
                    $usuarioNotificarContaChaveAlterarSenhaEvent->enviar();
                    return $this->responseJson($response, [
                        "alerta" => "Senha alterada com sucesso."
                    ]);
                }else{
                    throw new HttpBadRequestException($request, 'Não foi possível salvar a nova senha');
                }
            }else{
                throw new HttpBadRequestException($request, 'Chave ou usuário inválidos');
            }
            
        } else {
            /** @var UsuarioEntity $usuarioEntity */
            $usuarioEntity = $this->usuarioRepository->findByUsernameEmail($dataReq['_username'], true);

            if (!empty($usuarioEntity)) {
                $usuarioEntity->genChave();
                $usuarioNotificarContaChaveEvent->setUsuario(clone ($usuarioEntity));
                $novaChaveSalva = $this->usuarioRepository->getRepository()->update(['uuid' => $usuarioEntity->uuid->value()], $usuarioEntity->toSave('recuperar'));

                if ($novaChaveSalva) {
                    $usuarioNotificarContaChaveEvent->enviar();
                }
            }
        }

        return $this->responseJson($response, [
            "alerta" => "Caso seu usuário ou e-mail está correto, você receberá um e-mail com instruções para recuperação da conta."
        ]);
    }
}
