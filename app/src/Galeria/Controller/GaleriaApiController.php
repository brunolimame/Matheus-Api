<?php

namespace Modulo\Galeria\Controller;

use Slim\Exception\HttpException;
use Core\Repository\FactoryFindBy;
use Core\Controller\BaseController;
use Psr\Container\ContainerInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpBadRequestException;
use Modulo\Galeria\Provider\GaleriaAclProvider;
use Modulo\Galeria\Repository\GaleriaRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GaleriaApiController extends BaseController
{

    /** @var GaleriaRepository */
    public $galeriaRepository;
    /** @var AclCheck */
    public $acl;

    public function __construct(GaleriaRepository $galeriaRepository, GaleriaAclProvider $galeriaAclProvider)
    {
        $this->galeriaRepository = $galeriaRepository;
        $this->acl = $galeriaAclProvider->getAcl();
    }

    /**
     * @param null $id
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws HttpNotFoundException
     * @throws \Doctrine\DBAL\Exception
     */
    public function index(Request $request, Response $response)
    {
        $uuid = $request->getQueryParams()['uuid'];
        $definirStatus = $this->acl->isAllowed('ler-todos') ? null : 1;
        
        if (!is_null($uuid)) {
            if ($this->acl->isAllowed('ler-todos')) {
                $itens = $this->galeriaRepository->getRepository()->find($uuid);
            } else {
                $itens = FactoryFindBy::factory($this->galeriaRepository->getRepository(), ['where' => "uuid#{$uuid}"], $definirStatus)->getItem();
            }

            if (empty($itens)) {
                throw new HttpNotFoundException($request, "Registro inválido");
            }
        } else {
            $itens = FactoryFindBy::factory($this->galeriaRepository->getRepository(), $request->getQueryParams(), $definirStatus);
        }

        return self::responseJson($response, $itens->toApi());
    }

    /**
     * @param null $id
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws HttpNotFoundException
     * @throws \Doctrine\DBAL\Exception
     */
    public function salvar(Request $request, Response $response)
    {
        $uuid = $request->getQueryParams()['uuid'];
        if (empty($uuid)) {
            if (!$this->acl->isAllowed('novo')) {
                throw new HttpBadRequestException($request, 'Acesso negado');
            }
            $entity = $this->galeriaRepository->getRepository()::getEntity();
        } else {
            if (!$this->acl->isAllowed('editar')) {
                throw new HttpBadRequestException($request, 'Acesso negado');
            }
            $entity = $this->galeriaRepository->getRepository()->find($uuid);
            if (empty($entity)) {
                throw new HttpNotFoundException($request, "Registro inválido;");
            }
        }

        $entity->hydrator($request->getParsedBody());
        if ($entity->id->value() > 0) {
            $registroEditado = $this->galeriaRepository->getRepository()->update(['uuid' => $entity->uuid->value()], $entity->toSave('editar'));
            if ($registroEditado) {
                return self::responseJson($response, $entity->toApi());
            } else {
                throw new \Exception("Não foi possível editar o registro.");
            }
        } else {
            $novoRegistroId = $this->galeriaRepository->getRepository()->insert($entity->toSave('novo'));
            if ($novoRegistroId) {
                $novosDados = $this->galeriaRepository->getRepository()->find($novoRegistroId, 'id');
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
     * @throws \Doctrine\DBAL\Exception
     */
    public function status($status, Request $request, Response $response)
    {
        if (!$this->acl->isAllowed('status')) {
            throw new HttpBadRequestException($request, 'Acesso negado');
        }
        $uuid = $request->getQueryParams()['uuid'];
        $entity = $this->galeriaRepository->getRepository()->find($uuid);
        if (empty($entity)) {
            throw new HttpNotFoundException($request, "Registro inválido;");
        }
        $novoStatus = (int)($status == 'a');

        $entity->status->set($novoStatus);

        $registroEditado = $this->galeriaRepository->getRepository()->update(['uuid' => $entity->uuid->value()], $entity->toSave('status'));
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
     * @throws \Doctrine\DBAL\Exception
     * @throws \HttpException
     */
    public function delete(Request $request, Response $response)
    {
        if (!$this->acl->isAllowed('delete')) {
            throw new HttpBadRequestException($request, 'Acesso negado');
        }
        $uuid = $request->getQueryParams()['uuid'];

        $entity = $this->galeriaRepository->getRepository()->find($uuid);
        if (empty($entity)) {
            throw new HttpNotFoundException($request, "Registro inválido;");
        }
        $apagar = $this->galeriaRepository->getRepository()->delete(['uuid' => $entity->uuid->value()]);

        if (!$apagar) {
            throw new \HttpException($request, "Não foi possível apagar o registro.");
        }

        return self::responseJson($response, ['removido' => $entity->uuid->value()]);
    }
}
