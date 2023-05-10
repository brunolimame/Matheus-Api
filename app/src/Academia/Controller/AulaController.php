<?php

namespace Modulo\Academia\Controller;

use Modulo\Academia\Entity\CursoAulaEntity;
use Modulo\Academia\Entity\CursoAulaViewEntity;
use Modulo\Academia\Repository\CursoAulaRepository;
use Modulo\Academia\Repository\CursoAulaViewRepository;
use Boot\Provider\Jwt\JwtController;
use Core\Controller\BaseController;
use Doctrine\DBAL\Exception;
use Modulo\Academia\Entity\AulaEntity;
use Modulo\Academia\Repository\AulaRepository;
use Modulo\Academia\Repository\CursoRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;

class AulaController extends BaseController
{

  /** @var AulaRepository */
  public $aulaRepository;
  /** @var CursoAulaRepository */
  public $cursoAulaRepository;
  /** @var CursoRepository */
  public $cursoRepository;
  /** @var CursoAulaViewRepository */
  public $cursoAulaViewRepository;
  /** @var JwtController */
  public $jwt;

  public function __construct(AulaRepository $aulaRepository, CursoRepository $cursoRepository, CursoAulaRepository $cursoAulaRepository, CursoAulaViewRepository $cursoAulaViewRepository, JwtController $jwtController)
  {
    $this->aulaRepository = $aulaRepository;
    $this->cursoAulaRepository = $cursoAulaRepository;
    $this->cursoAulaViewRepository = $cursoAulaViewRepository;
    $this->cursoRepository = $cursoRepository;
    $this->jwt = $jwtController;
  }

  /**
   * @param Request $request
   * @param Response $response
   * @param $curso_uuid
   * @return Response
   * @throws HttpBadRequestException
   * @throws \Exception
   */
  public function findAll(Request $request, Response $response, $curso_uuid): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'designer', 'atendimento', 'qa', 'desenvolvimento', 'fotografia'])) {

      $itens = $this->cursoAulaViewRepository->getRepository()->findBy(['curso_uuid' => $curso_uuid])->itensToArray();
      $arrayFiltrado = $this->filtrarAulas($request, $itens);
      $arrayOrdenado = $this->ordenarAulas($arrayFiltrado);

      return self::responseJson($response, $arrayOrdenado, 200);
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }
  }

  /**
   * @param Request $request
   * @param array $aulas
   * @return array
   */
  private function filtrarAulas(Request $request, array $aulas): array
  {
    $userInfo = $this->jwt->decodeNovo($request);
    $arrays = [];
    $arrayFiltrado = [];
    foreach ($aulas as $aula) {
      if (!isset($arrays[$aula->aula_uuid])) {
        $arrays[$aula->aula_uuid] = [];
      }
      array_push($arrays[$aula->aula_uuid], $aula);
    }

    $info = [];
    foreach ($arrays as $chave => $array) {
      foreach ($aulas as $aula) {
        if ($aula->aula_uuid == $chave) {
          $info = [
            'uuid' => $aula->uuid,
            'curso_uuid' => $aula->curso_uuid,
            'aula_uuid' => $aula->aula_uuid,
            'aula_nome' => $aula->aula_nome,
            'aula_descricao' => $aula->aula_descricao,
            'proximo' => $aula->proximo,
            'isprimeiro' => $aula->isprimeiro,
            'aula_concluida' => ''
          ];
        }
      }

      foreach ($array as $item) {
        if ($item->usuario_uuid == $userInfo->uuid) {
          if ($item->aula_concluida == 1) {
            $info['aula_concluida'] = 's';
          } else {
            $info['aula_concluida'] = 'n';
          }
        }
      }
      array_push($arrayFiltrado, (object)$info);
    }

    return $arrayFiltrado;
  }

  /**
   * @param array $aulas
   * @return array
   */
  private function ordenarAulas(array $aulas): array
  {
    $arrayOrdenado = [];
    if (count($aulas) > 0) {
      /** @var CursoAulaViewEntity $item */
      foreach ($aulas as $item) {
        if ($item->isprimeiro == 1) {
          array_push($arrayOrdenado, $item);
        }
      }

      while ($arrayOrdenado[count($arrayOrdenado) - 1]->proximo != '') {
        foreach ($aulas as $item) {
          if ($item->uuid == $arrayOrdenado[count($arrayOrdenado) - 1]->proximo) {
            array_push($arrayOrdenado, $item);
          }
        }
      }
    }

    return $arrayOrdenado;
  }

  /**
   * @param Request $request
   * @param Response $response
   * @param $curso_uuid
   * @param $aula_uuid
   * @return Response
   * @throws HttpBadRequestException
   * @throws \Exception
   */
  public function getProximo(Request $request, Response $response, $curso_uuid, $aula_uuid): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'designer', 'atendimento', 'qa', 'desenvolvimento', 'fotografia'])) {

      /** @var CursoAulaViewEntity $item */
      $item = $this->cursoAulaViewRepository->getRepository()->findBy(['curso_uuid' => $curso_uuid, 'aula_uuid' => $aula_uuid])->getItem();
      /** @var CursoAulaViewEntity $cursoAula */
      $cursoAula = $this->cursoAulaViewRepository->getRepository()->findBy(['uuid' => $item->proximo->value()])->getItem();
      return self::responseJson($response, $cursoAula->aula_uuid->value(), 200);
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }
  }

  /**
   * @param Request $request
   * @param Response $response
   * @param $curso_uuid
   * @return Response
   * @throws HttpBadRequestException
   * @throws \Exception
   */

  public function create(Request $request, Response $response, $curso_uuid): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
      $argumentos = $request->getParsedBody();

      /** @var AulaEntity $entity */
      $entity = $this->aulaRepository->getRepository()::getEntity();
      $entity->hydrator($argumentos);
      $entity->hydrator(['status' => 1]);

      try {
        $novoRegistroId = $this->aulaRepository->getRepository()->insert($entity->toArray());
      } catch (\Exception $e) {
        throw new \Exception("Não foi possivel salvar");
      }

      if ($novoRegistroId) {
        /** @var CursoAulaEntity $cursoAulaEntity */
        $cursoAulaEntity = $this->cursoAulaRepository->getRepository()::getEntity();
        $cursoAulaEntity->hydrator(['proximo' => '']);

        $aulasNoCurso = $this->cursoAulaRepository->getRepository()->findBy(['curso_uuid' => $curso_uuid], [], 5000)->itensToArray();
        $aulaAnterior = null;

        if (count($aulasNoCurso) == 0) { //Caso seja a primeira aula cadastrada no curso
          $cursoAulaEntity->hydrator(['isprimeiro' => 1]);
        } else {
          $cursoAulaEntity->hydrator(['isprimeiro' => 0]);
          $aulaAnterior = $this->cursoAulaRepository->getRepository()->findBy(['curso_uuid' => $curso_uuid, 'proximo' => ''], [], 1)->getItem();
        }

        $cursoAulaEntity->hydrator([
          'curso_uuid' => $curso_uuid,
          'aula_uuid' => $entity->uuid->value()
        ]);
        $resultCursoAula = $this->cursoAulaRepository->getRepository()->insert($cursoAulaEntity->toArray());

        if ($resultCursoAula) {
          if ($aulaAnterior) {
            $this->cursoAulaRepository->getRepository()->update(['uuid' => $aulaAnterior->uuid->value()], ['proximo' => $cursoAulaEntity->uuid->value()]);
          }

          return self::responseJson($response, true);
        } else {
          $this->aulaRepository->getRepository()->delete(['uuid' => $entity->uuid->value()]);
          throw new \Exception("Não foi possível criar o registro.");
        }

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
   * @return Response
   * @throws Exception
   * @throws HttpBadRequestException
   * @throws \Exception
   */

  public function update(Request $request, Response $response, $aula_uuid): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
      $argumentos = $request->getParsedBody();

      /** @var AulaEntity $entity */
      $entity = $this->aulaRepository->getRepository()->find($aula_uuid);
      $entity->hydrator($argumentos);

      try {
        $novoRegistroId = $this->aulaRepository->getRepository()->update(['uuid' => $aula_uuid], $entity->toArray());
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
  public function ordenar(Request $request, Response $response): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
      $argumentos = $request->getParsedBody();
      $itemUUID = $argumentos['item'];
      $anteriorDoItemUUID = $argumentos['anteriorDoItem'];
      $destinoUUID = $argumentos['destino'];
      $anteriorDoDestinoUUID = $argumentos['anteriorDoDestino'];

      /** @var CursoAulaEntity $item */
      $item = $this->cursoAulaRepository->getRepository()->find($itemUUID);
      /** @var CursoAulaEntity $anteriorDoItem */
      $anteriorDoItem = $anteriorDoItemUUID ? $this->cursoAulaRepository->getRepository()->find($anteriorDoItemUUID) : false;
      /** @var CursoAulaEntity $proximoDoItem */
      $proximoDoItem = $this->cursoAulaRepository->getRepository()->find($item->proximo->value());
      /** @var CursoAulaEntity $destino */
      $destino = $this->cursoAulaRepository->getRepository()->find($destinoUUID);
      /** @var CursoAulaEntity $anteriorDoDestino */
      $anteriorDoDestino = $anteriorDoDestinoUUID ? $this->cursoAulaRepository->getRepository()->find($anteriorDoDestinoUUID) : false;

      $cloneItem = clone($item);
      $cloneDestino = clone($destino);

      if ($anteriorDoItem) { //Caso o item não seja o primeiro
        $anteriorDoItem->proximo->set($cloneItem->proximo->value());
      } else { //Caso o item seja o primeiro
        $item->isprimeiro->set(0);
        $proximoDoItem->isprimeiro->set(1);
      }

      $item->proximo->set($cloneDestino->uuid->value());

      if ($anteriorDoDestino) { //Caso o destino não seja o primeiro item
        $anteriorDoDestino->proximo->set($cloneItem->uuid->value());
      } else { //Caso o destino seja o primeiro item
        $item->isprimeiro->set(1);
        $destino->isprimeiro->set(0);
      }

      try {
        $this->cursoAulaRepository->getRepository()->update(['uuid' => $item->uuid->value()], $item->toArray());
        $this->cursoAulaRepository->getRepository()->update(['uuid' => $destino->uuid->value()], $destino->toArray());
        if ($anteriorDoItem) {
          $this->cursoAulaRepository->getRepository()->update(['uuid' => $anteriorDoItem->uuid->value()], $anteriorDoItem->toArray());
        } else {
          $this->cursoAulaRepository->getRepository()->update(['uuid' => $proximoDoItem->uuid->value()], $proximoDoItem->toArray());
        }
        if ($anteriorDoDestino) {
          $this->cursoAulaRepository->getRepository()->update(['uuid' => $anteriorDoDestino->uuid->value()], $anteriorDoDestino->toArray());
        }
      } catch (\Exception $e) {
        throw new \Exception("Não foi possivel salvar");
      }

      return self::responseJson($response, true);
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
   */
  public function findOne(Request $request, Response $response, $uuid): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'designer', 'desenvolvimento', 'fotografia'])) {

      /** @var AulaEntity $aula */
      $aula = $this->aulaRepository->getRepository()->find($uuid);
      return self::responseJson($response, $aula->toApi());

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
   * @throws \Exception
   */
  public function delete(Request $request, Response $response, $uuid): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
      $argumentos = $request->getParsedBody();

      /** @var AulaEntity $entity */
      $entity = $this->aulaRepository->getRepository()->find($uuid);
      $entity->hydrator([

      ]);

      try {
        $novoRegistroId = $this->aulaRepository->getRepository()->update(['uuid' => $uuid], $entity->toArray());
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
