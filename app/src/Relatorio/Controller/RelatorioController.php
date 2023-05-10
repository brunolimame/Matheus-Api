<?php

namespace Modulo\Relatorio\Controller;

use Boot\Provider\Jwt\JwtController;
use Core\Request\RequestApi;
use Core\Controller\BaseController;
use Core\Controller\Lib\ControllerView;
use Exception;
use Modulo\Passo\Repository\PassoRepository;
use Modulo\Relatorio\Request\RelatorioRequestApi;
use Modulo\Tarefa\Entity\TarefaEntity;
use Modulo\Tarefa\Repository\TarefaRepository;
use Modulo\TipoTarefa\Entity\TipoTarefaEntity;
use Modulo\TipoTarefa\Repository\TipoTarefaRepository;
use Modulo\User\Repository\UserRepository;
use Modulo\Usuario\Entity\UsuarioEntity;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;

class RelatorioController extends BaseController
{
  /**@var ControllerView */
  public $view;
  /**@var RequestApi */
  public $api;
  /** @var JwtController */
  public $jwt;
  /** @var UserRepository */
  public $userRepository;
  /** @var TarefaRepository */
  public $tarefaRepository;
  /** @var PassoRepository */
  public $passoRepository;
  /** @var TipoTarefaRepository */
  public $tipoRepository;

  public function __construct(
    RelatorioRequestApi  $relatorioRequestApi,
    JwtController        $jwtController,
    UserRepository       $userRepository,
    TarefaRepository     $tarefaRepository,
    PassoRepository      $passoRepository,
    TipoTarefaRepository $tipoRepository
  )
  {
    $this->api = $relatorioRequestApi->getApi();
    $this->jwt = $jwtController;
    $this->userRepository = $userRepository;
    $this->tarefaRepository = $tarefaRepository;
    $this->passoRepository = $passoRepository;
    $this->tipoRepository = $tipoRepository;
  }

  /**
   * @param Request $request
   * @param Response $response
   * @param $modo
   * @param $ano
   * @param $mes
   * @param null $colaborador
   * @return Response
   * @throws HttpBadRequestException
   * @throws Exception
   */
  public function findAll(Request $request, Response $response, $modo, $ano, $mes, $colaborador = null): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
      if ($mes == 'todos') {
        $data = $ano . '-';
      } else {
        $data = $this->formarData($ano, $mes);
      }

      if (is_null($colaborador)) {
        $colaboradores = $this->userRepository->getRepository()->findBy(['status' => 1])->getItens();
        $resposta = $this->carregar($colaboradores, $modo, $data);
      } else {
        $resposta = $this->carregar($colaborador, $modo, $data);
      }
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }

    return self::responseJsonNovo($response, $resposta, 200);
  }

  /**
   * @throws Exception
   */
  public function carregar(array $colaboradores, $modo, $data)
  {
    $resposta = [];
    /** @var UsuarioEntity $colaborador */
    foreach ($colaboradores as $colaborador) {
      $tarefas = $this->tarefaRepository->getRepository()->findBy(['user_uuid' => $colaborador->uuid->value(), 'setor' => 'criacao', 'status' => 'concluida'], [], 10000, 1, ['busca' => $data, 'campos_busca' => ['data']])->getItens();

      if ($modo == 'quantidade') {
        $total = 0;
        $volta_atendimento = 0;
        $volta_cliente = 0;
        /** @var TarefaEntity $tarefa */
        foreach ($tarefas as $tarefa) {
          $total++;
          $volta_atendimento += $this->passoRepository->getRepository()->findBy(['tarefa_uuid' => $tarefa->uuid->value(), 'titulo' => 'AGUARDANDO REVISÃO > EM EXECUÇÃO'], [], 10000)->totalDeRegistros();
          $volta_cliente += $this->passoRepository->getRepository()->findBy(['tarefa_uuid' => $tarefa->uuid->value(), 'titulo' => 'AGUARDANDO CLIENTE > EM FILA'], [], 10000)->totalDeRegistros();
        }
        array_push($resposta, [
          'colaborador' => ['uuid' => $colaborador->uuid->value(), 'nome' => $colaborador->nome->value()],
          'total' => $total,
          'volta_atendimento' => $volta_atendimento,
          'volta_cliente' => $volta_cliente
        ]);

      } else {
        $total_pontos = 0;
        $volta_atendimento = 0;
        /** @var TarefaEntity $tarefa */
        foreach ($tarefas as $tarefa) {
          /** @var TipoTarefaEntity $tipo */
          $tipo = $this->tipoRepository->getRepository()->findBy(['uuid' => $tarefa->tipo_uuid->value()])->getItem();
          $total_pontos += $tipo->pontos->value();
          $volta_atendimento += $this->passoRepository->getRepository()->findBy(['tarefa_uuid' => $tarefa->uuid->value(), 'titulo' => 'AGUARDANDO REVISÃO > EM EXECUÇÃO'], [], 10000)->totalDeRegistros();
        }

        array_push($resposta, [
          'colaborador' => ['uuid' => $colaborador->uuid->value(), 'nome' => $colaborador->nome->value()],
          'total' => $total_pontos,
          'volta_atendimento' => $volta_atendimento
        ]);
      }
    }

    return $resposta;
  }

  public function formarData($ano, $mes, $dia = null): string
  {
    switch ($mes) {
      case 'janeiro':
        $mes = '01';
        break;
      case 'fevereiro':
        $mes = '02';
        break;
      case 'marco':
        $mes = '03';
        break;
      case 'abril':
        $mes = '04';
        break;
      case 'maio':
        $mes = '05';
        break;
      case 'junho':
        $mes = '06';
        break;
      case 'julho':
        $mes = '07';
        break;
      case 'agosto':
        $mes = '08';
        break;
      case 'setembro':
        $mes = '09';
        break;
      case 'outubro':
        $mes = '10';
        break;
      case 'novembro':
        $mes = '11';
        break;
      case 'dezembro':
        $mes = '12';
        break;
      default:
        $mes = '00';
    }
    if ($dia) {
      return $ano . '-' . $mes . '-' . $dia;
    } else {
      return $ano . '-' . $mes . '-';
    }
  }
}
