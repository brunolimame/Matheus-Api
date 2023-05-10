<?php

namespace Modulo\TipoTarefa\Controller;

use Boot\Provider\Jwt\JwtController;
use Core\Lib\Acl\AclCheck;
use Core\Request\RequestApi;
use Core\Controller\BaseController;
use Doctrine\DBAL\Exception;
use HttpException;
use Modulo\TipoTarefa\Entity\TipoTarefaEntity;
use Modulo\TipoTarefa\Repository\TipoTarefaRepository;
use Core\Controller\Lib\ControllerView;
use Modulo\TipoTarefa\Request\TipoTarefaRequestApi;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class TipoTarefaController extends BaseController
{
  /**@var ControllerView */
  public $view;
  /**@var RequestApi */
  public $api;
  /** @var TipoTarefaRepository */
  public $setoresRepository;
  /** @var AclCheck */
  public $acl;
  /** @var JwtController */
  public $jwt;

  public function __construct(
    TipoTarefaRepository $setoresRepository,
    TipoTarefaRequestApi $setoresRequestApi,
    JwtController     $jwtController
  )
  {
    $this->api = $setoresRequestApi->getApi();
    $this->setoresRepository = $setoresRepository;
    $this->jwt = $jwtController;
  }

  /**
   * @param Request $request
   * @param Response $response
   * @return Response
   * @throws HttpBadRequestException
   */
  public function findAll(Request $request, Response $response): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'designer', 'desenvolvimento', 'fotografia'])) {
      $tipos = $this->setoresRepository->getRepository()->findBy([], ['codigo' => 'ASC'], 5000)->itensToArray();
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }

    return self::responseJson($response, $tipos, 200);
  }

  /**
   * @param Request $request
   * @param Response $response
   * @return Response
   * @throws HttpBadRequestException
   */
  public function getAll(Request $request, Response $response): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'designer', 'desenvolvimento', 'fotografia'])) {
      $tipos = $this->setoresRepository->getRepository()->findBy(['status' => 1], ['codigo' => 'ASC'], 5000)->itensToArray();
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }

    return self::responseJson($response, $tipos, 200);
  }

  /**
   * @param $uuid
   * @param Request $request
   * @param Response $response
   * @return Response
   * @throws Exception
   * @throws HttpBadRequestException
   */
  public function find($uuid, Request $request, Response $response): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'designer', 'desenvolvimento', 'fotografia'])) {
      /** @var TipoTarefaEntity $setor */
      $tipo = $this->setoresRepository->getRepository()->find($uuid);
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }

    return self::responseJson($response, $tipo->toApi(), 200);
  }

  /**
   * @param Request $request
   * @param Response $response
   * @return Response
   * @throws Exception
   * @throws HttpBadRequestException
   * @throws HttpNotFoundException
   */
  public function create(Request $request, Response $response): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
      $argumentos = $request->getParsedBody();

      $uuid = $argumentos['uuid'];
      if ($uuid == '') {
        unset($argumentos['uuid']);
        unset($uuid);
      }

      /** @var TipoTarefaEntity $entity */
      if (empty($uuid)) {
        $entity = $this->setoresRepository->getRepository()::getEntity();
      } else {
        $entity = $this->setoresRepository->getRepository()->find($uuid);
        if (empty($entity)) {
          throw new HttpNotFoundException($request, "Registro inválido;");
        }
      }

      $entity->hydrator($argumentos);

      if (!$argumentos['nome']) {
        throw new \Exception("Informe o nome");
      }

      if ($entity->id->value() > 0) {
        $registroSalvo = $this->setoresRepository->getRepository()->update(['uuid' => $entity->uuid->value()], $entity->toSave('editar'));
      } else {
        $registroSalvo = $this->setoresRepository->getRepository()->insert($entity->toSave('novo'));
      }

      if ($registroSalvo) {
        return self::responseJson($response, $entity->toApi());
      } else {
        throw new \Exception("Não foi possivel salvar");
      }
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }
  }

  /**
   * @param Request $request
   * @param Response $response
   * @return Response
   * @throws Exception
   * @throws HttpBadRequestException
   * @throws HttpNotFoundException
   */
  public function update(Request $request, Response $response): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
      $argumentos = $request->getParsedBody();

      $uuid = $argumentos['uuid'];

      $entity = $this->setoresRepository->getRepository()->find($uuid);
      if (empty($entity)) {
        throw new HttpNotFoundException($request, "Registro inválido;");
      }

      $entity->hydrator($argumentos);

      $registroSalvo = $this->setoresRepository->getRepository()->update(['uuid' => $uuid], $entity->toSave('editar'));

      if ($registroSalvo) {
        return self::responseJson($response, $entity->toApi());
      } else {
        throw new \Exception("Não foi possivel salvar");
      }
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }
  }

  /**
   * @param $uuid
   * @param Request $request
   * @param Response $response
   * @return Response
   * @throws Exception
   * @throws HttpBadRequestException
   * @throws HttpException
   * @throws HttpNotFoundException
   */
  public function delete($uuid, Request $request, Response $response): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
      /** @var TipoTarefaEntity $entity */
      $entity = $this->setoresRepository->getRepository()->find($uuid);
      if (empty($entity)) {
        throw new HttpNotFoundException($request, "Registro inválido;");
      }
      $apagar = $this->setoresRepository->getRepository()->delete(['uuid' => $entity->uuid->value()]);

      if (!$apagar) {
        throw new HttpException($request, "Não foi possível apagar o registro.");
      }

      return self::responseJson($response, ['removido' => $entity->uuid->value()]);
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }
  }
}
