<?php

namespace Modulo\Academia\Controller;

use Boot\Provider\Jwt\JwtController;
use Core\Controller\BaseController;
use Doctrine\DBAL\Exception;
use Modulo\Academia\Entity\FaqEntity;
use Modulo\Academia\Repository\FaqRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;

class FaqController extends BaseController
{
  /** @var FaqRepository */
  public $faqRepository;
  /** @var JwtController */
  public $jwt;

  public function __construct(FaqRepository $faqRepository, JwtController $jwtController)
  {
    $this->faqRepository = $faqRepository;
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
      $itens = $this->faqRepository->getRepository()->findBy([], [], 5000)->itensToArray();
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
      /** @var FaqEntity $item */
      $item = $this->faqRepository->getRepository()->find($uuid);
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

      /** @var FaqEntity $entity */
      $entity = $this->faqRepository->getRepository()::getEntity();
      $entity->hydrator(['status' => 1]);
      $entity->hydrator($argumentos);

      try {
        $novoRegistroId = $this->faqRepository->getRepository()->insert($entity->toArray());
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

      /** @var FaqEntity $entity */
      $entity = $this->faqRepository->getRepository()->find($uuid);
      $entity->hydrator($argumentos);

      try {
        $novoRegistroId = $this->faqRepository->getRepository()->update(['uuid' => $uuid], $entity->toSave());
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
      /** @var FaqEntity $entity */
      $entity = $this->faqRepository->getRepository()->find($uuid);
      $entity->hydrator(['status' => 0]);

      try {
        $novoRegistroId = $this->faqRepository->getRepository()->update(['uuid' => $uuid], $entity->toSave());
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
