<?php

namespace Modulo\Cliente\Controller;

use Boot\Provider\Jwt\JwtController;
use Core\Lib\Acl\AclCheck;
use Core\Request\RequestApi;
use Core\Controller\BaseController;
use Doctrine\DBAL\Exception;
use Modulo\Cliente\Entity\ClienteEntity;
use Modulo\Cliente\Repository\ClienteRepository;
use Core\Controller\Lib\ControllerView;
use Modulo\Cliente\Request\ClienteRequestApi;
use Modulo\User\Entity\UserEntity;
use Modulo\User\Repository\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;
use Modulo\Cliente\Provider\ClienteAclProvider;
use Modulo\Tarefa\Controller\TarefaController;

class ClienteController extends BaseController
{

  public RequestApi $api;
  public ClienteRepository $clienteRepository;
  public AclCheck $acl;
  public JwtController $jwt;

  public function __construct(
    ClienteAclProvider $clienteAclProvider,
    ClienteRepository  $clienteRepository,
    ClienteRequestApi  $clienteRequestApi,
    JwtController      $jwtController
  )
  {
    $this->api = $clienteRequestApi->getApi();
    $this->clienteRepository = $clienteRepository;
    $this->acl = $clienteAclProvider->getAcl();
    $this->jwt = $jwtController;
  }

  /**
   * @param Request $request
   * @param Response $response
   * @param UserRepository $repoUser
   * @param $uuid
   * @return Response
   * @throws Exception
   * @throws HttpBadRequestException
   * @throws HttpNotFoundException
   * @throws \Exception
   */
  public function find(Request $request, Response $response, UserRepository $repoUser, $uuid): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'atendimento'])) {
      /** @var ClienteEntity $itens */
      $itens = $this->clienteRepository->getRepository()->find($uuid);

      if (empty($itens)) {
        throw new HttpNotFoundException($request, "Registro inválido");
      } else {
        /** @var UserEntity $designer */
        $designer = $repoUser->getRepository()->findBy(['uuid' => $itens->usuario_uuid->value()])->getItem();
        $itens = (object)[
          'uuid' => $itens->uuid->value(),
          'usuario_uuid' => $itens->usuario_uuid->value(),
          'usuario_nome' => $designer->nome->value(),
          'usuario_avatar' => $designer->foto->value(),
          'razao_social' => $itens->razao_social->value(),
          'posts' => $itens->posts->value(),
          'assinatura' => $itens->assinatura->value(),
          'informacao' => $itens->informacao->value(),
          'status' => $itens->status->value()
        ];
      }
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }

    return self::responseJson($response, $itens);
  }

  /**
   * @param Request $request
   * @param Response $response
   * @param UserRepository $repoUser
   * @param $pagina
   * @param $designer_uuid
   * @param null $busca
   * @return Response
   * @throws HttpBadRequestException
   * @throws \Exception
   */
  public function findByPage(Request $request, Response $response, UserRepository $repoUser, $pagina, $designer_uuid, $busca = null): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'atendimento'])) {
      if ($busca) {
        if ($designer_uuid != 'todos') {
          $olditens = $this->clienteRepository->getRepository()->findBy(['usuario_uuid' => $designer_uuid], ['razao_social' => 'ASC'], 15, $pagina, ['busca' => $busca, 'campos_busca' => ['razao_social']]);
        } else {
          $olditens = $this->clienteRepository->getRepository()->findBy([], ['razao_social' => 'ASC'], 15, $pagina, ['busca' => $busca, 'campos_busca' => ['razao_social']]);
        }
      } else {
        if ($designer_uuid != 'todos') {
          $olditens = $this->clienteRepository->getRepository()->findBy(['usuario_uuid' => $designer_uuid], ['razao_social' => 'ASC'], 15, $pagina);
        } else {
          $olditens = $this->clienteRepository->getRepository()->findBy([], ['razao_social' => 'ASC'], 15, $pagina);
        }
      }
      $tempArr = [];
      /** @var ClienteEntity $item */
      foreach ($olditens->itens as $item) {
        /** @var UserEntity $designer */
        $designer = $repoUser->getRepository()->findBy(['uuid' => $item->usuario_uuid->value()])->getItem();
        if ($designer) {
          array_push($tempArr, [
            'uuid' => $item->uuid->value() ?: null,
            'usuario_uuid' => $designer->nome->value() ?: null,
            'razao_social' => $item->razao_social->value() ?: null,
            'posts' => $item->posts->value() ?: null,
            'status' => $item->status->value() ?: null
          ]);
        }
      }

      $itens = (object)['pagina' => ['total' => $olditens->pagina->total, 'atual' => $olditens->pagina->current, 'results' => $olditens->pagina->results], 'itens' => $tempArr];

    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }

    return self::responseJson($response, $itens);
  }

  /**
   * @param Request $request
   * @param Response $response
   * @param UserRepository $repoUser
   * @return Response
   * @throws HttpBadRequestException
   * @throws \Exception
   */
  public function findAll(Request $request, Response $response, UserRepository $repoUser): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'designer', 'atendimento', 'fotografia'])) {
      $olditens = $this->clienteRepository->getRepository()->findBy(['status' => 1], ['razao_social' => 'ASC'], 5000);
      $tempArr = [];
      /** @var ClienteEntity $item */
      foreach ($olditens->itens as $item) {
        array_push($tempArr, [
          'uuid' => $item->uuid->value(),
          'razao_social' => $item->razao_social->value(),
          'usuario_uuid' => $item->usuario_uuid->value()
        ]);
      }
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }
    return self::responseJson($response, $tempArr);
  }

  /**
   * @param Request $request
   * @param Response $response
   * @param TarefaController $tarefaController
   * @return Response
   * @throws Exception
   * @throws HttpBadRequestException
   */
  public function create(Request $request, Response $response, TarefaController $tarefaController): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'atendimento'])) {
      $argumentos = $request->getParsedBody();
      unset($argumentos['uuid']);

      /** @var ClienteEntity $entity */
      $entity = $this->clienteRepository->getRepository()::getEntity();
      $entity->hydrator([
        'status' => true
      ]);

      $entity->hydrator($argumentos);

      if (!$argumentos['usuario_uuid'] || !$argumentos['razao_social'] || !$argumentos['posts']) {
        if (!$argumentos['usuario_uuid']) {
          throw new Exception("Informe o Designer");
        } elseif (!$argumentos['razao_social']) {
          throw new Exception("Informe a Razão Social");
        } else {
          throw new Exception("Informe a quantidade de Posts");
        }
      }

      try {
        $novoRegistroId = $this->clienteRepository->getRepository()->insert($entity->toSave('novo'));
      } catch (Exception $e) {
        throw new Exception("Não foi possivel salvar");
      }

      if ($novoRegistroId) {
        $novosDados = $this->clienteRepository->getRepository()->find($novoRegistroId, 'id');
        return self::responseJson($response, true);
      } else {
        throw new Exception("Não foi possível criar o registro.");
      }
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }
  }

  /**
   * @param Request $request
   * @param Response $response
   * @param TarefaController $tarefaController
   * @param $uuid
   * @return Response
   * @throws Exception
   * @throws HttpBadRequestException
   * @throws HttpNotFoundException
   * @throws \Exception
   */
  public function update(Request $request, Response $response, TarefaController $tarefaController, $uuid): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'atendimento'])) {
      $argumentos = $request->getParsedBody();
      $designerAntigo = '';

      /** @var ClienteEntity $entity */
      $entity = $this->clienteRepository->getRepository()->find($uuid);
      if (empty($entity)) {
        throw new HttpNotFoundException($request, "Registro inválido");
      }
      $designerAntigo = $entity->usuario_uuid->value();


      $entity->hydrator($argumentos);

      if (!$argumentos['usuario_uuid'] || !$argumentos['razao_social'] || !$argumentos['posts']) {
        if (!$argumentos['usuario_uuid']) {
          throw new Exception("Informe o Designer");
        } elseif (!$argumentos['razao_social']) {
          throw new Exception("Informe a Razão Social");
        } else {
          throw new Exception("Informe a quantidade de Posts");
        }
      }

      if ($entity->usuario_uuid->value() !== $designerAntigo) {
        $tarefaController->atualizarTarefasByCliente($entity->uuid->value(), $designerAntigo, $entity->usuario_uuid->value());
      }
      $registroEditado = $this->clienteRepository->getRepository()->update(['uuid' => $entity->uuid->value()], $entity->toSave('editar'));
      if ($registroEditado) {
        return self::responseJson($response, true);
      } else {
        throw new \Exception("Não foi possível editar o registro.");
      }
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }
  }

  /**
   * @param Request $request
   * @param Response $response
   * @param $uuid
   * @return Response
   * @throws Exception
   * @throws HttpBadRequestException
   * @throws HttpNotFoundException
   * @throws \Exception
   */
  public function status(Request $request, Response $response, $uuid): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner'])) {

      /** @var ClienteEntity $entity */
      $entity = $this->clienteRepository->getRepository()->find($uuid);
      if (empty($entity)) {
        throw new HttpNotFoundException($request, "Registro inválido;");
      }

      $entity->status->set((int)(!$entity->status->value()));

      $registroEditado = $this->clienteRepository->getRepository()->update(['uuid' => $entity->uuid->value()], $entity->toSave('status'));
      if ($registroEditado) {
        return self::responseJson($response, $entity->toApi());
      } else {
        throw new \Exception("Não foi possível alteara o status.");
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
   * @throws \HttpException
   */
  public function delete(Request $request, Response $response)
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner'])) {
      $uuid = $request->getParsedBody()['uuid'];

      $entity = $this->clienteRepository->getRepository()->find($uuid);
      if (empty($entity)) {
        throw new HttpNotFoundException($request, "Registro inválido;");
      }
      $apagar = $this->clienteRepository->getRepository()->delete(['uuid' => $entity->uuid->value()]);

      if (!$apagar) {
        throw new \HttpException($request, "Não foi possível apagar o registro.");
      }

      return self::responseJson($response, ['removido' => $entity->uuid->value()]);
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }
  }
}
