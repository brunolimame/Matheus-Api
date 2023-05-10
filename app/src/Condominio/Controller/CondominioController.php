<?php

namespace Modulo\Condominio\Controller;

use Core\Lib\Acl\AclCheck;
use Core\Repository\FactoryFindBy;
use Core\Request\RequestApi;
use Core\Controller\BaseController;
use Doctrine\DBAL\Exception;
use Modulo\Condominio\Repository\CondominioRepository;
use Psr\Container\ContainerInterface;
use Core\Controller\Lib\ControllerView;
use Modulo\Condominio\Request\CondominioRequestApi;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;
use Modulo\Condominio\Provider\CondominioAclProvider;

class CondominioController extends BaseController
{
    /**@var ControllerView */
    public $view;
    /**@var RequestApi */
    public $api;
    /** @var CondominioRepository */
    public $condominioRepository;
    /** @var AclCheck */
    public $acl;

    public function __construct(
        CondominioAclProvider $condominioAclProvider,
        CondominioRepository $condominioRepository,
        CondominioRequestApi $condominioRequestApi
    ){
        $this->api = $condominioRequestApi->getApi();
        $this->condominioRepository = $condominioRepository;
        $this->acl = $condominioAclProvider->getAcl();
    }

    /**
     * @param null $id
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws HttpNotFoundException
     * @throws Exception
     */
    public function index(Request $request, Response $response)
    {
        $uuid = $request->getQueryParams()['uuid'];
        $definirStatus = $this->acl->isAllowed('ler-todos') ? null : 1;

        if (!is_null($uuid)) {
            if ($this->acl->isAllowed('ler-todos')) {
                $itens = $this->condominioRepository->getRepository()->find($uuid);
            } else {
                $itens = FactoryFindBy::factory($this->condominioRepository->getRepository(), ['where' => "uuid#{$uuid}"], $definirStatus)->getItem();
            }

            if (empty($itens)) {
                throw new HttpNotFoundException($request, "Registro inválido");
            }
        } else {
            $itens = FactoryFindBy::factory($this->condominioRepository->getRepository(), $request->getQueryParams(), $definirStatus);
        }

        return self::responseJson($response, $itens->toApi());
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws HttpBadRequestException
     * @throws HttpNotFoundException
     * @throws Exception
     */
    public function salvar(Request $request, Response $response)
    {
        $uuid = $request->getQueryParams()['uuid'];
        var_dump($request);die;
        if (empty($uuid)) {
            if (!$this->acl->isAllowed('novo')) {
                throw new HttpBadRequestException($request, 'Acesso negado');
            }
            $entity = $this->condominioRepository->getRepository()::getEntity();
        } else {
            if (!$this->acl->isAllowed('editar')) {
                throw new HttpBadRequestException($request, 'Acesso negado');
            }
            $entity = $this->condominioRepository->getRepository()->find($uuid);
            if (empty($entity)) {
                throw new HttpNotFoundException($request, "Registro inválido;");
            }
        }

        $entity->hydrator($request->getParsedBody());
        if ($entity->id->value() > 0) {
            $registroEditado = $this->condominioRepository->getRepository()->update(['uuid' => $entity->uuid->value()], $entity->toSave('editar'));
            if ($registroEditado) {
                return self::responseJson($response, $entity->toApi());
            } else {
                throw new \Exception("Não foi possível editar o registro.");
            }
        } else {
            $novoRegistroId = $this->condominioRepository->getRepository()->insert($entity->toSave('novo'));
            if ($novoRegistroId) {
                $novosDados = $this->condominioRepository->getRepository()->find($novoRegistroId, 'id');
                return self::responseJson($response, $novosDados->toApi());
            } else {
                throw new \Exception("Não foi possível criar o registro.");
            }
        }
    }

    /**
     * @param $id
     * @param $status
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws HttpNotFoundException
     * @throws Exception
     */
    public function status($status, Request $request, Response $response)
    {
        if (!$this->acl->isAllowed('status')) {
            throw new HttpBadRequestException($request, 'Acesso negado');
        }
        $uuid = $request->getQueryParams()['uuid'];
        $entity = $this->condominioRepository->getRepository()->find($uuid);
        if (empty($entity)) {
            throw new HttpNotFoundException($request, "Registro inválido;");
        }
        $novoStatus = (int)($status == 'a');

        $entity->status->set($novoStatus);

        $registroEditado = $this->condominioRepository->getRepository()->update(['uuid' => $entity->uuid->value()], $entity->toSave('status'));
        if ($registroEditado) {
            return self::responseJson($response, $entity->toApi());
        } else {
            throw new \Exception("Não foi possível alteara o status.");
        }
    }

    /**
     * @param $id
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws HttpNotFoundException
     * @throws Exception
     * @throws \HttpException
     */
    public function delete(Request $request, Response $response)
    {
        if (!$this->acl->isAllowed('delete')) {
            throw new HttpBadRequestException($request, 'Acesso negado');
        }
        $uuid = $request->getQueryParams()['uuid'];

        $entity = $this->condominioRepository->getRepository()->find($uuid);
        if (empty($entity)) {
            throw new HttpNotFoundException($request, "Registro inválido;");
        }
        $apagar = $this->condominioRepository->getRepository()->delete(['uuid' => $entity->uuid->value()]);

        if (!$apagar) {
            throw new \HttpException($request, "Não foi possível apagar o registro.");
        }

        return self::responseJson($response, ['removido' => $entity->uuid->value()]);
    }
}
