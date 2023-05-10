<?php

namespace Modulo\Academia\Controller;

use Modulo\Academia\Entity\UsuarioAulaEntity;
use Modulo\Academia\Repository\CursoAulaRepository;
use Modulo\Academia\Repository\CursoAulaViewRepository;
use Boot\Provider\Jwt\JwtController;
use Core\Controller\BaseController;
use Doctrine\DBAL\Exception;
use Modulo\Academia\Entity\AulaEntity;
use Modulo\Academia\Repository\AulaRepository;
use Modulo\Academia\Entity\CursoEntity;
use Modulo\Academia\Repository\CursoRepository;

use Modulo\Academia\Repository\UsuarioAulaRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;

class UsuarioAulaController extends BaseController
{
  /** @var UsuarioAulaRepository */
  public $usuarioAulaRepository;
  /** @var JwtController */
  public $jwt;

  public function __construct(UsuarioAulaRepository $usuarioAulaRepository, JwtController $jwtController)
  {
    $this->usuarioAulaRepository = $usuarioAulaRepository;
    $this->jwt = $jwtController;
  }

//  /**
//   * @param Request $request
//   * @param Response $response
//   * @param $curso_uuid
//   * @return Response
//   * @throws HttpBadRequestException
//   * @throws \Exception
//   */
//  public function findAll(Request $request, Response $response, $curso_uuid): Response
//  {
//    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'designer', 'atendimento', 'qa', 'desenvolvimento', 'fotografia'])) {
//      $itens = $this->cursoAulaViewRepository->getRepository()->findBy(['curso_uuid' => $curso_uuid])->itensToArray();
//
//      return self::responseJson($response, $itens, 200);
//    } else {
//      throw new HttpBadRequestException($request, 'Acesso negado');
//    }
//  }

  /**
   * @param Request $request
   * @param Response $response
   * @return Response
   * @throws HttpBadRequestException
   * @throws \Exception
   */

  public function create(Request $request, Response $response): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'designer', 'atendimento', 'qa', 'desenvolvimento', 'fotografia'])) {
      $argumentos = $request->getParsedBody();
      $entity = $this->usuarioAulaRepository->getRepository()->findBy(['usuario_uuid' => $argumentos['usuario_uuid'], 'aula_uuid' => $argumentos['aula_uuid']])->getItem();
      if ($entity) {
        return self::responseJson($response, true);
      }
      /** @var AulaEntity $entity */
      $entity = $this->usuarioAulaRepository->getRepository()::getEntity();
      $entity->hydrator(['concluido' => 0]);
      $entity->hydrator($argumentos);

      try {
        $novoRegistroId = $this->usuarioAulaRepository->getRepository()->insert($entity->toSave());
      } catch (\Exception $e) {
        throw new \Exception($e->getMessage());
//        throw new \Exception("Não foi possivel salvar");
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
   * @param $aula_uuid
   * @param $usuario_uuid
   * @return Response
   * @throws HttpBadRequestException
   * @throws \Exception
   */
  public function setConcluido(Request $request, Response $response, $usuario_uuid, $aula_uuid): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'designer', 'atendimento', 'qa', 'desenvolvimento', 'fotografia'])) {

      try {
        /** @var UsuarioAulaEntity $entity */
        $entity = $this->usuarioAulaRepository->getRepository()->findBy(['usuario_uuid' => $usuario_uuid, 'aula_uuid' => $aula_uuid], [], 1)->getItem();
        $entity->hydrator(['concluido' => 1]);
        $novoRegistroId = $this->usuarioAulaRepository->getRepository()->update(['uuid' => $entity->uuid], $entity->toSave());
      } catch (\Exception $e) {
        throw new \Exception($e->getMessage());
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
   * @param $usuario_uuid
   * @param $aula_uuid
   * @return Response
   * @throws HttpBadRequestException
   */
  public function findOne(Request $request, Response $response, $usuario_uuid, $aula_uuid): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'designer', 'desenvolvimento', 'fotografia'])) {

      /** @var UsuarioAulaEntity $usuarioAula */
      $usuarioAula = $this->aulaRepository->getRepository()->findBy(['usuario_uuid' => $usuario_uuid, 'aula_uuid' => $aula_uuid], [], 1)->getItem();
      var_dump($usuarioAula);
      die;
      return self::responseJson($response, $aula->toApi());

    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }
  }
}
