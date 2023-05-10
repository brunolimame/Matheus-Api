<?php

namespace Modulo\Passo\Controller;

use Boot\Provider\Jwt\JwtController;
use Core\Controller\BaseController;
use Core\Inferface\ParametrosArquivoInterface;
use Core\Lib\Arquivo\ArquivoNovosDados;
use Doctrine\DBAL\Exception;
use Modulo\Passo\Entity\PassoArquivoEntity;
use Modulo\Passo\Entity\PassoEntity;
use Modulo\Passo\Repository\PassoArquivoRepository;
use Modulo\Passo\Repository\PassoRepository;
use Modulo\Tarefa\Repository\TarefaRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Exception\HttpBadRequestException;

class PassoController extends BaseController
{
  /** @var TarefaRepository */
  public $tarefaRepository;
  /** @var PassoRepository */
  public $passoRepository;
  /** @var PassoArquivoRepository */
  public $passoArquivoRepository;
  /** @var JwtController */
  public $jwt;

  public function __construct(TarefaRepository $tarefaRepository, PassoRepository $passoRepository, PassoArquivoRepository $passoArquivoRepository, JwtController $jwtController)
  {
    $this->tarefaRepository = $tarefaRepository;
    $this->passoRepository = $passoRepository;
    $this->passoArquivoRepository = $passoArquivoRepository;
    $this->jwt = $jwtController;
  }

  /**
   * @param Request $request
   * @param Response $response
   * @param $uuid
   * @return Response
   * @throws \Exception
   */
  public function find(Request $request, Response $response, $uuid): Response
  {
    $passos = $this->passoRepository->getRepository()->findBy(['tarefa_uuid' => $uuid], ['criado' => 'DESC'], 5000)->getItens();

    $itens = [];
    /** @var PassoEntity $passo */
    foreach ($passos as $passo) {
      /** @var PassoArquivoEntity $arquivos */
      $arquivos = $this->passoArquivoRepository->getRepository()->findBy(['passo_uuid' => $passo->uuid->value()], [], 5000)->itensToArray();

      array_push($itens, [
        'uuid' => $passo->uuid->value(),
        'titulo' => $passo->titulo->value(),
        'informacao' => $passo->informacao->value() ? $passo->informacao->value() : '',
        'criado' => $passo->criado->format("d/m/Y \à\s H:i"),
        'arquivos' => $arquivos
      ]);
    }

    return self::responseJson($response, (object)$itens);
  }

  /**
   * @param Request $request
   * @param Response $response
   * @param PassoArquivoRepository $passoArquivoRepository
   * @return Response
   * @throws Exception
   * @throws HttpBadRequestException
   * @throws \Exception
   */
  public function create(Request $request, Response $response, $tarefa_uuid, PassoArquivoRepository $passoArquivoRepository): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'atendimento', 'qa', 'planner', 'desenvolvimento', 'designer', 'fotografia'])) {
      $argumentos = $request->getParsedBody();
//      $tarefa_uuid = $argumentos['tarefa_uuid'];

      $tarefa = $this->tarefaRepository->getRepository()->find($tarefa_uuid);

      if (!empty($tarefa)) {
        /** @var PassoEntity $entity */
        $entity = $this->passoRepository->getRepository()::getEntity();
        $entity->hydrator($argumentos);

        $resultadoPasso = $this->passoRepository->getRepository()->insert($entity->toSave());

        if ($resultadoPasso) {
          return self::responseJson($response, true);
        } else {
          throw new \Exception("Falha ao salvar passo.");
        }
      } else {
        throw new HttpBadRequestException($request, 'Registro não encontrado');
      }
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }
  }
}
