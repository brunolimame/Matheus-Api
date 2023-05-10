<?php

namespace Modulo\Agendamento\Controller;

use Boot\Provider\Jwt\JwtController;
use Core\Lib\Acl\AclCheck;
use Core\Request\RequestApi;
use Core\Controller\BaseController;
use Doctrine\DBAL\Exception;
use HttpException;
use Modulo\Agendamento\Entity\AgendamentoEntity;
use Modulo\Agendamento\Repository\AgendamentoRepository;
use Core\Controller\Lib\ControllerView;
use Modulo\Agendamento\Request\AgendamentoRequestApi;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class AgendamentoController extends BaseController
{
  public ControllerView $view;
  public RequestApi $api;
  public AgendamentoRepository $agendamentoRepository;
  public AclCheck $acl;
  public JwtController $jwt;

  public function __construct(
    AgendamentoRepository $agendamentoRepository,
    AgendamentoRequestApi $agendamentoRequestApi,
    JwtController         $jwtController
  )
  {
    $this->api = $agendamentoRequestApi->getApi();
    $this->agendamentoRepository = $agendamentoRepository;
    $this->jwt = $jwtController;
  }

  /**
   * @param $dia
   * @param $mes
   * @param $ano
   * @param Request $request
   * @param Response $response
   * @return Response
   * @throws HttpBadRequestException
   * @throws \Exception
   */
  public function findAll($dia, $mes, $ano, Request $request, Response $response): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner'])) {
      $userInfo = $this->jwt->decodeNovo($request);
      $agendamentos = $this->agendamentoRepository->getRepository()->findBy(['user_uuid' => $userInfo->uuid, 'data' => $ano . '-' . $mes . '-' . $dia], ['ordem' => 'ASC'], 5000)->itensToArray();
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }
    return self::responseJson($response, $agendamentos);
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
      /** @var AgendamentoEntity $setor */
      $agendamento = $this->agendamentoRepository->getRepository()->find($uuid);
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }
    return self::responseJson($response, $agendamento->toApi());
  }

  /**
   * @param Request $request
   * @param Response $response
   * @return Response
   * @throws HttpBadRequestException
   * @throws \Exception
   */
  public function create(Request $request, Response $response): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner'])) {
      $argumentos = $request->getParsedBody();

      if (!$argumentos['texto']) {
        throw new \Exception("Informe o texto");
      }
      if (!$argumentos['data']) {
        throw new \Exception("Informe a data");
      }
      $userInfo = $this->jwt->decodeNovo($request);
      $total = count($this->agendamentoRepository->getRepository()->findBy(['user_uuid' => $userInfo->uuid, 'data' => $argumentos['data']],[],5000)->getItens());

      unset($argumentos['uuid']);
      /** @var AgendamentoEntity $entity */
      $entity = $this->agendamentoRepository->getRepository()::getEntity();
      $entity->hydrator($argumentos);
      $entity->hydrator(['ordem' => $total, 'user_uuid' => $userInfo->uuid]);
      $registroSalvo = $this->agendamentoRepository->getRepository()->insert($entity->toSave('novo'));

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
   * @throws \Exception
   */
  public function update(Request $request, Response $response): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner'])) {
      $argumentos = $request->getParsedBody();

      $uuid = $argumentos['uuid'];

      $entity = $this->agendamentoRepository->getRepository()->find($uuid);
      if (empty($entity)) {
        throw new HttpNotFoundException($request, "Registro inválido;");
      }

      $entity->hydrator($argumentos);

      $registroSalvo = $this->agendamentoRepository->getRepository()->update(['uuid' => $uuid], $entity->toSave('editar'));

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
   * @param $dia
   * @param $mes
   * @param $ano
   * @param Request $request
   * @param Response $response
   * @return Response
   * @throws HttpBadRequestException
   * @throws HttpNotFoundException
   * @throws \Exception
   */
  public function reorder($dia, $mes, $ano, Request $request, Response $response): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner'])) {
      $argumentos = $request->getParsedBody();
      $userInfo = $this->jwt->decodeNovo($request);

      /** @var AgendamentoEntity $entity */
      $entity = $this->agendamentoRepository->getRepository()->findBy(['user_uuid' => $userInfo->uuid, 'data' => $ano.'-'.$mes.'-'.$dia, 'ordem' => $argumentos['initialPos']])->getItem();

      if (empty($entity)) {
        throw new HttpNotFoundException($request, "Registro inválido;");
      }

      $entity->hydrator(['ordem' => $argumentos['finalPos']]);
      $this->agendamentoRepository->getRepository()->delete(['uuid' => $entity->uuid->value()]);

      $agendamentos = $this->agendamentoRepository->getRepository()->findBy(['user_uuid' => $userInfo->uuid, 'data' => $ano.'-'.$mes.'-'.$dia], ['ordem' => 'ASC'], 5000)->getItens();

      for($i = 0; $i < count($agendamentos); $i++) {
        /** @var AgendamentoEntity $agendamento */
        $agendamento = $agendamentos[$i];
        if ($agendamento->ordem->value() >= $argumentos['finalPos']) {
          $agendamento->hydrator(['ordem' => $i+1]);
        } else {
          $agendamento->hydrator(['ordem' => $i]);
        }
        $this->agendamentoRepository->getRepository()->update(['uuid' => $agendamento->uuid->value()], $agendamento->toSave());
      }

      $registroSalvo = $this->agendamentoRepository->getRepository()->insert($entity->toSave('reorder'));

      if ($registroSalvo) {
        return self::responseJson($response, true);
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
   * @throws \Exception
   */
  public function delete($uuid, Request $request, Response $response): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner'])) {
      /** @var AgendamentoEntity $entity */
      $entity = $this->agendamentoRepository->getRepository()->find($uuid);
      if (empty($entity)) {
        throw new HttpNotFoundException($request, "Registro inválido;");
      }
      $apagar = $this->agendamentoRepository->getRepository()->delete(['uuid' => $entity->uuid->value()]);

      if (!$apagar) {
        throw new HttpException($request, "Não foi possível apagar o registro.");
      }

      return self::responseJson($response, ['removido' => $entity->uuid->value()]);
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }
  }
}
