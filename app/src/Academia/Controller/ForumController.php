<?php

namespace Modulo\Academia\Controller;

use Boot\Provider\Jwt\JwtController;
use Core\Controller\BaseController;
use Doctrine\DBAL\Exception;
use Modulo\Academia\Entity\ForumCategoriaEntity;
use Modulo\Academia\Entity\ForumTopicoEntity;
use Modulo\Academia\Repository\ForumCategoriaRepository;
use Modulo\Academia\Repository\ForumTopicoRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;

class ForumController extends BaseController
{
  /** @var ForumCategoriaRepository */
  public $forumCategoriaRepository;
  /** @var ForumTopicoRepository */
  public $forumTopicoRepository;
  /** @var JwtController */
  public $jwt;

  public function __construct(ForumCategoriaRepository $forumCategoriaRepository, ForumTopicoRepository $forumTopicoRepository, JwtController $jwtController)
  {
    $this->forumCategoriaRepository = $forumCategoriaRepository;
    $this->forumTopicoRepository = $forumTopicoRepository;
    $this->jwt = $jwtController;
  }

  /**
   * @param Request $request
   * @param Response $response
   * @return Response
   * @throws HttpBadRequestException
   * @throws \Exception
   */
  public function findAll(Request $request, Response $response): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'designer', 'atendimento', 'qa', 'desenvolvimento', 'fotografia'])) {
      $categorias = $this->forumCategoriaRepository->getRepository()->findBy(['status' => 1], [], 5000)->getItens();
      $itens = [];
      /** @var ForumCategoriaEntity $categoria */
      foreach ($categorias as $categoria) {
        $topicos = $this->forumTopicoRepository->getRepository()->findBy(['categoria_uuid' => $categoria->uuid->value()], ['criado' => 'DESC'], 5)->itensToArray();
        array_push($itens, ['nome' => $categoria->nome->value(), 'topicos' => $topicos]);
      }

      return self::responseJson($response, $itens, 200);
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }
  }

  /**
   * @param Request $request
   * @param Response $response
   * @return Response
   * @throws HttpBadRequestException
   * @throws \Exception
   */
  public function findAllCategorias(Request $request, Response $response): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
      $itens = $this->forumCategoriaRepository->getRepository()->findBy([], [], 5000)->itensToArray();

      return self::responseJson($response, $itens, 200);
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }
  }

  /**
   * @param Request $request
   * @param Response $response
   * @param $uuid
   * @return Response
   * @throws HttpBadRequestException
   * @throws Exception
   */
  public function findOne(Request $request, Response $response, $uuid): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
      /** @var ForumTopicoEntity $item */
      $item = $this->forumTopicoRepository->getRepository()->find($uuid);
      return self::responseJson($response, $item->toArray(), 200);
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }
  }

  /**
   * @param Request $request
   * @param Response $response
   * @param $uuid
   * @return Response
   * @throws HttpBadRequestException
   * @throws Exception
   */
  public function findOneCategoria(Request $request, Response $response, $uuid): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
      /** @var ForumCategoriaEntity $item */
      $item = $this->forumCategoriaRepository->getRepository()->find($uuid);
      return self::responseJson($response, $item->toArray(), 200);
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }
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
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
      $argumentos = $request->getParsedBody();

      /** @var ForumTopicoEntity $entity */
      $entity = $this->forumTopicoRepository->getRepository()::getEntity();
      $entity->hydrator($argumentos);
      $entity->hydrator(['status' => 1]);

      try {
        $novoRegistroId = $this->forumTopicoRepository->getRepository()->insert($entity->toArray());
      } catch (\Exception $e) {
        throw new \Exception("Não foi possivel salvar");
      }

      if ($novoRegistroId) {
        return self::responseJson($response, true);
      } else {
        throw new \Exception("Não foi possível criar o registro.");
      }
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }
  }

  /**
   * @param Request $request
   * @param Response $response
   * @return Response
   * @throws HttpBadRequestException
   * @throws \Exception
   */
  public function createCategoria(Request $request, Response $response): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
      $argumentos = $request->getParsedBody();

      /** @var ForumCategoriaEntity $entity */
      $entity = $this->forumCategoriaRepository->getRepository()::getEntity();
      $entity->hydrator($argumentos);
      $entity->hydrator(['status' => 1]);

      try {
        $novoRegistroId = $this->forumCategoriaRepository->getRepository()->insert($entity->toArray());
      } catch (\Exception $e) {
        throw new \Exception("Não foi possivel salvar");
      }

      if ($novoRegistroId) {
        return self::responseJson($response, true);
      } else {
        throw new \Exception("Não foi possível criar o registro.");
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
   * @throws HttpBadRequestException
   * @throws \Exception
   */
  public function update(Request $request, Response $response, $uuid): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
      $argumentos = $request->getParsedBody();

      /** @var ForumTopicoEntity $entity */
      $entity = $this->forumTopicoRepository->getRepository()->find($uuid);
      $entity->hydrator($argumentos);

      try {
        $novoRegistroId = $this->forumTopicoRepository->getRepository()->update(['uuid' => $uuid], $entity->toSave());
      } catch (\Exception $e) {
        throw new \Exception("Não foi possivel salvar");
      }

      if ($novoRegistroId) {
        return self::responseJson($response, true);
      } else {
        throw new \Exception("Não foi possível criar o registro.");
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
   * @throws HttpBadRequestException
   * @throws \Exception
   */
  public function updateCategoria(Request $request, Response $response, $uuid): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
      $argumentos = $request->getParsedBody();

      /** @var ForumCategoriaEntity $entity */
      $entity = $this->forumCategoriaRepository->getRepository()->find($uuid);
      $entity->hydrator($argumentos);

      try {
        $novoRegistroId = $this->forumCategoriaRepository->getRepository()->update(['uuid' => $uuid], $entity->toSave());
      } catch (\Exception $e) {
        throw new \Exception("Não foi possivel salvar");
      }

      if ($novoRegistroId) {
        return self::responseJson($response, true);
      } else {
        throw new \Exception("Não foi possível criar o registro.");
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
   * @throws HttpBadRequestException
   * @throws \Exception
   */
  public function delete(Request $request, Response $response, $uuid): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
      /** @var ForumTopicoEntity $entity */
      $entity = $this->forumTopicoRepository->getRepository()->find($uuid);
      $entity->hydrator(['excluido' => 1]);

      try {
        $novoRegistroId = $this->forumTopicoRepository->getRepository()->update(['uuid' => $uuid], $entity->toSave());
      } catch (\Exception $e) {
        throw new \Exception("Não foi possivel salvar");
      }

      if ($novoRegistroId) {
        return self::responseJson($response, true);
      } else {
        throw new \Exception("Não foi possível criar o registro.");
      }
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }
  }
}
