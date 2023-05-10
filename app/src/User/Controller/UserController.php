<?php

namespace Modulo\User\Controller;

use Boot\Provider\Jwt\JwtController;
use Core\Entity\EntityColection;
use Core\Lib\Acl\AclCheck;
use Core\Lib\Arquivo\Imagem;
use Core\Request\RequestApi;
use Core\Controller\BaseController;
use Doctrine\DBAL\Exception;
use HttpException;
use Modulo\User\Entity\UserEntity;
use Modulo\User\Repository\UserRepository;
use Core\Controller\Lib\ControllerView;
use Modulo\User\Request\UserRequestApi;
use PHPImageWorkshop\Core\Exception\ImageWorkshopLayerException;
use PHPImageWorkshop\Exception\ImageWorkshopException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;
use Modulo\User\Provider\UserAclProvider;

class UserController extends BaseController
{
  /**@var ControllerView */
  public $view;
  /**@var RequestApi */
  public $api;
  /** @var UserRepository */
  public $userRepository;
  /** @var AclCheck */
  public $acl;
  /** @var JwtController */
  public $jwt;

  public function __construct(
    UserAclProvider $userAclProvider,
    UserRepository  $userRepository,
    UserRequestApi  $userRequestApi,
    JwtController   $jwtController
  )
  {
    $this->api = $userRequestApi->getApi();
    $this->userRepository = $userRepository;
    $this->acl = $userAclProvider->getAcl();
    $this->jwt = $jwtController;
  }

  /**
   * @param Request $request
   * @param Response $response
   * @return Response
   * @throws Exception
   * @throws HttpBadRequestException
   * @throws HttpNotFoundException
   */
  public function index(Request $request, Response $response): Response
  {
    $argumentos = $request->getParsedBody();
    $uuid = $argumentos['uuid'];
    $pagina = $argumentos['pagina'];
    $itens = [];

    if (!is_null($uuid)) {
      $decode = $this->jwt->decodeNovo($request);

      if ($this->jwt->validaAcesso($request, ['sadmin', 'admin']) || $decode->uuid == $uuid) {
        /** @var UserEntity $item */
        $item = $this->userRepository->getRepository()->find($uuid);
        $itens = [
          'uuid' => $item->uuid->value(),
          'nome' => $item->nome->value(),
          'username' => $item->username->value(),
          'email' => $item->email->value(),
          'nivel' => $item->nivel->value(),
          'niveis' => $item->nivel->value(),
          'foto' => $item->foto->value(),
          'status' => $item->status->value()
        ];
        if (empty($item)) {
          throw new HttpNotFoundException($request, "Registro inválido");
        }
      } else {
        throw new HttpBadRequestException($request, 'Acesso negado');
      }
    } else {
      if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
        /** @var EntityColection $items */
        $items = $this->userRepository->getRepository()->findBy([], ['nome' => 'ASC'], 15, $pagina, ['busca' => $argumentos['busca'], 'campos_busca' => ['nome']]);
        $itens['itens'] = [];
        $itens['page'] = ['total' => $items->totalPaginas(), 'results' => $items->totalDeRegistros()];
        foreach ($items->getItens() as $item) {
          array_push($itens['itens'], [
            'uuid' => $item->uuid->value(),
            'nome' => $item->nome->value(),
            'username' => $item->username->value(),
            'email' => $item->email->value(),
            'nivel' => $item->nivel->value(),
            'niveis' => $item->nivel->value(),
            'foto' => $item->foto->value() ? $item->foto->value() : null,
            'status' => $item->status->value()
          ]);
        }
      } else {
        throw new HttpBadRequestException($request, 'Acesso negado');
      }
    }

    return self::responseJson($response, $itens);
  }

  /**
   * @param Request $request
   * @param Response $response
   * @return Response
   * @throws HttpBadRequestException
   */
  public function all(Request $request, Response $response): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
      $itens = $this->userRepository->getRepository()->findBy(['status' => 1], ['nome' => 'ASC'], 5000)->itensToArray();
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }

    return self::responseJson($response, ['itens' => $itens]);
  }

  /**
   * @param Request $request
   * @param Response $response
   * @param $uuid
   * @return Response
   * @throws HttpBadRequestException
   * @throws \Exception
   */
  public function findOne(Request $request, Response $response, $uuid): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
      /** @var UserEntity $resposta */
      $resposta = $this->userRepository->getRepository()->findBy(['uuid' => $uuid], [], 1)->getItem();
      $item = [
        'uuid' => $resposta->uuid->value(),
        'nome' => $resposta->nome->value(),
      ];
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }

    return self::responseJsonNovo($response, $item);
  }

  /**
   * @param Request $request
   * @param Response $response
   * @return Response
   * @throws HttpBadRequestException
   */
  public function getColaboradores(Request $request, Response $response): Response
  {
    $itensApi = [];
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'qa', 'atendimento', 'planner'])) {
      $itens = $this->userRepository->getRepository()->findBy(['status' => 1], ['nome' => 'ASC'], 5000)->getItens();
      /** @var UserEntity $item */
      foreach ($itens as $item) {
        if ($item->nome->value() != 'ITweb') {
          array_push($itensApi, [
            'uuid' => $item->uuid->value(),
            'nome' => $item->nome->value(),
            'nivel' => $item->nivel->value()
          ]);
        }
      }
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }

    return self::responseJson($response, $itensApi);
  }

  /**
   * @param Request $request
   * @param Response $response
   * @return Response
   * @throws HttpBadRequestException
   */
  public function getDesigner(Request $request, Response $response): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'atendimento'])) {
      $itens = $this->userRepository->findByDesigner();
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }

    return self::responseJson($response, $itens->toApi());
  }

  /**
   * @param Request $request
   * @param Response $response
   * @return Response
   * @throws Exception
   * @throws HttpBadRequestException
   * @throws HttpNotFoundException
   * @throws ImageWorkshopLayerException
   * @throws ImageWorkshopException
   */
  public function salvar(Request $request, Response $response): Response
  {
    if (($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) || ($this->jwt->decodeNovo($request)->uuid == $request->getParsedBody()['uuid'])) {
      $argumentos = $request->getParsedBody();
      $uuid = $argumentos['uuid'];
      /** @var UserEntity $entity */
      if (empty($uuid)) {
        $entity = $this->userRepository->getRepository()::getEntity();
        $entity->hydrator([
          'status' => true
        ]);
      } else {
        $entity = $this->userRepository->getRepository()->find($uuid);
        if (empty($entity)) {
          throw new HttpNotFoundException($request, "Registro inválido;");
        }
      }

      $entity->hydrator($request->getParsedBody());

      if ($argumentos['niveis']) {
        $entity->nivel->set($argumentos['niveis']);
      }

      $parametrosArquivo = $entity::getParametrosParaArquivo();

      if ($request->getUploadedFiles()['foto']) {
        /** @var UploadedFileInterface $foto */
        $foto = $request->getUploadedFiles()['foto'];
        if ($foto->getSize() >= 0) {
          $novasImagens = Imagem::redimencionar($foto, $parametrosArquivo);
          $entity->foto->set($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . json_decode($novasImagens->arquivos->value(), true)['']);
        }
      }

      if ($entity->id->value() > 0) {
        if (!empty($request->getParsedBody()['password'])) {
          $entity->password->set($entity->encodePass($request->getParsedBody()['password'], $entity->salt->value()));
        }
        $registroEditado = $this->userRepository->getRepository()->update(['uuid' => $entity->uuid->value()], $entity->toSave('editar'));
        if ($registroEditado) {
          return self::responseJson($response, $registroEditado);
        } else {
          throw new \Exception("Não foi possível editar o registro.");
        }
      } else {
        try {
          $novoRegistroId = $this->userRepository->getRepository()->insert($entity->toSave('novo'));
        } catch (\Exception $e) {
          throw new \Exception("Não foi possivel salvar");
        }

        if ($novoRegistroId) {
          return self::responseJson($response, $novoRegistroId);
        } else {
          throw new \Exception("Não foi possível criar o registro.");
        }
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
  public
  function status(Request $request, Response $response)
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner'])) {
      $uuid = $request->getParsedBody()['uuid'];
      /** @var UserEntity $entity */
      $entity = $this->userRepository->getRepository()->find($uuid);
      if (empty($entity) || $entity->nivel->value() == 'sadmin') {
        throw new HttpNotFoundException($request, "Registro inválido;");
      }

      $entity->status->set((int)!$entity->status->value());

      $registroEditado = $this->userRepository->getRepository()->update(['uuid' => $entity->uuid->value()], $entity->toSave('status'));
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
   * @throws HttpException
   */
  public
  function delete(Request $request, Response $response): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin'])) {
      $uuid = $request->getParsedBody()['uuid'];

      /** @var UserEntity $entity */
      $entity = $this->userRepository->getRepository()->find($uuid);
      if (empty($entity)) {
        throw new HttpNotFoundException($request, "Registro inválido");
      } else {
        if ($entity->nivel->value() !== 'sadmin') {
          $apagar = $this->userRepository->getRepository()->delete(['uuid' => $entity->uuid->value()]);
        } else {
          throw new HttpBadRequestException($request, 'Acesso negado');
        }
      }

      if (!$apagar) {
        throw new HttpException($request, "Não foi possível apagar o registro.");
      }

      return self::responseJson($response, ['removido' => $entity->uuid->value()]);
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }
  }
}
