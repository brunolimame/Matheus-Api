<?php

namespace Modulo\Academia\Controller;

use Boot\Provider\Jwt\JwtController;
use Core\Controller\BaseController;
use Core\Inferface\ParametrosArquivoInterface;
use Core\Lib\Arquivo\ArquivoNovosDados;
use Doctrine\DBAL\Exception;
use Modulo\Cliente\Entity\ClienteEntity;
use Modulo\Cliente\Repository\ClienteRepository;
use Modulo\Post\Entity\PostEntity;
use Modulo\Post\Repository\PostRepository;
use Modulo\Academia\Entity\CursoEntity;
use Modulo\Academia\Repository\CursoRepository;
use Modulo\User\Entity\UserEntity;
use Modulo\User\Repository\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class CursoController extends BaseController
{
  /** @var CursoRepository */
  public $cursoRepository;
  /** @var JwtController */
  public $jwt;

  public function __construct(CursoRepository $cursoRepository, JwtController $jwtController)
  {
    $this->cursoRepository = $cursoRepository;
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
      $itens = $this->cursoRepository->getRepository()->findBy([], [], 5000)->itensToArray();
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
   * @throws Exception
   * @throws HttpBadRequestException
   */
  public function findOne(Request $request, Response $response, $uuid): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'designer', 'desenvolvimento', 'fotografia'])) {
      /** @var CursoEntity $curso */
      $curso = $this->cursoRepository->getRepository()->find($uuid);
      return self::responseJson($response, $curso->toApi());
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
      $userInfo = $this->jwt->decodeNovo($request);

      /** @var CursoEntity $entity */
      $entity = $this->cursoRepository->getRepository()::getEntity();

      /** @var UploadedFileInterface $arquivo */
      $arquivo = current($request->getUploadedFiles());

      if ($arquivo) {
        $novosDados = ArquivoNovosDados::load($arquivo);
        $parametrosArquivo = $entity::getParametrosParaArquivo();
        $arquivo->moveTo($parametrosArquivo->getLocalAbsoluto() . $novosDados->nomeNovo);
        $entity->hydrator(['capa' => $parametrosArquivo->getLocal() . $novosDados->nomeNovo]);
      }

      $entity->hydrator(['status' => 1]);
      $entity->log->add(['nome' => $userInfo->nome, 'uuid' => $userInfo->uuid], 'Criou curso');
      $entity->hydrator($argumentos);

      try {
        $novoRegistroId = $this->cursoRepository->getRepository()->insert($entity->toArray());
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
   */
  public function update(Request $request, Response $response, $uuid): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
      $argumentos = $request->getParsedBody();
      $userInfo = $this->jwt->decodeNovo($request);

      /** @var CursoEntity $entity */
      $entity = $this->cursoRepository->getRepository()::getEntity();

      /** @var UploadedFileInterface $arquivo */
      $arquivo = current($request->getUploadedFiles());

      if ($arquivo) {
        $novosDados = ArquivoNovosDados::load($arquivo);
        $parametrosArquivo = $entity::getParametrosParaArquivo();
        $arquivo->moveTo($parametrosArquivo->getLocalAbsoluto() . $novosDados->nomeNovo);
        $entity->hydrator(['capa' => $parametrosArquivo->getLocal() . $novosDados->nomeNovo]);
      }

      $entity->hydrator(['status' => 1]);
      $entity->log->add(['nome' => $userInfo->nome, 'uuid' => $userInfo->uuid], 'Criou curso');
      $entity->hydrator($argumentos);

      try {
        $novoRegistroId = $this->cursoRepository->getRepository()->insert($entity->toArray());
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
