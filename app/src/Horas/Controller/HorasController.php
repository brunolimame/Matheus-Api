<?php

namespace Modulo\Horas\Controller;

use Core\Controller\BaseController;
use DateTime;
use Modulo\Cliente\Entity\ClienteEntity;
use Modulo\Cliente\Repository\ClienteRepository;
use Modulo\Horas\Entity\HorasEntity;
use Modulo\Horas\Repository\HorasRepository;
use Modulo\Passo\Repository\PassoRepository;
use Modulo\Tarefa\Entity\TarefaEntity;
use Modulo\Tarefa\Repository\TarefaRepository;
use Modulo\User\Entity\UserEntity;
use Modulo\User\Repository\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HorasController extends BaseController
{
  public function __construct()
  {
  }

  /**
   * @param Request $request
   * @param Response $response
   * @param $data
   * @param $cliente_uuid
   * @param null $designer_uuid
   * @param HorasRepository $repoHoras
   * @param PassoRepository $repoPasso
   * @return Response
   * @throws \Exception
   */
  public function index(Request $request, Response $response, HorasRepository $repoHoras, PassoRepository $repoPasso, $data, $cliente_uuid, $designer_uuid = null): Response
  {
    $resultParcial = [];
    if ($designer_uuid) {
      $tarefas = $repoHoras->getRepository()->findBy(['designer_uuid' => $designer_uuid, 'cliente_uuid' => $cliente_uuid], ['data' => 'ASC'], 5000, 1, ['busca' => $data, 'campos_busca' => ['data']])->itensToArray();
    } else {
      $tarefas = $repoHoras->getRepository()->findBy(['cliente_uuid' => $cliente_uuid], ['data' => 'ASC'], 5000, 1, ['busca' => $data, 'campos_busca' => ['data']])->itensToArray();
    }

    $tarefas_planejamento = 0;
    $tarefas_extra = 0;

    $tempo_planejamento = 0;
    $tempo_extra = 0;
    $tempoTotal = 0;

    $alteracao_planejamento_itweb = 0;
    $alteracao_planejamento_cliente = 0;
    $alteracao_extra_itweb = 0;
    $alteracao_extra_cliente = 0;
    $alteracao_total = 0;

    /** @var HorasEntity $tarefa */
    foreach ($tarefas as $tarefa) {
      $passos = $repoPasso->getRepository()->findBy(['tarefa_uuid' => $tarefa->uuid], ['criado' => 'ASC'], 5000)->itensToArray();
      $tempo = 0;
      $alteracao_itweb = 0;
      $alteracao_cliente = 0;
      for ($i = 0; $i < count($passos); $i++) {
        if (str_contains($passos[$i]->titulo, ' > EM EXECUÇÃO')) {
          $inicio = $passos[$i]->criado->object;
          $fim = $passos[$i + 1]->criado->object;
          $tempo += $this->calcularTempo($inicio, $fim);
        }
        if (str_contains($passos[$i]->titulo, 'AGUARDANDO CLIENTE > EM FILA')) {
          $alteracao_cliente++;
        }
        if (str_contains($passos[$i]->titulo, 'AGUARDANDO REVISÃO > EM EXECUÇÃO')) {
          $alteracao_itweb++;
        }
      }
      if ($tarefa->planejamento) {
        $tarefas_planejamento++;
        $tempo_planejamento += $tempo;
        $alteracao_planejamento_itweb += $alteracao_itweb;
        $alteracao_planejamento_cliente += $alteracao_cliente;
      } else {
        $tarefas_extra++;
        $tempo_extra += $tempo;
        $alteracao_extra_itweb += $alteracao_itweb;
        $alteracao_extra_cliente += $alteracao_cliente;
      }

      $tempoTotal += $tempo;
      $alteracao_total += ($alteracao_cliente + $alteracao_itweb);
      array_push($resultParcial, [
        'tema' => $tarefa->tema,
        'data' => $tarefa->data,
        'planejamento' => $tarefa->planejamento,
        'tempo' => gmdate("H:i:s", $tempo),
        'alteracao_interna' => $alteracao_itweb,
        'alteracao_externa' => $alteracao_cliente
      ]);
    }

    $result = [
      'designer_uuid' => $tarefas[0]->designer_uuid,
      'cliente_uuid' => $tarefas[0]->cliente_uuid,
      'designer' => $tarefas[0]->nome,
      'cliente' => $tarefas[0]->razao_social,

      'qtd_posts' => $tarefas[0]->qtd_posts,
      'tarefas' => $resultParcial,
      'tarefas_planejamento' => $tarefas_planejamento,
      'tarefas_extra' => $tarefas_extra,

      'tempo_planejamento' => gmdate("H:i:s", ($tempo_planejamento / $tarefas_planejamento)),
      'tempo_extra' => gmdate("H:i:s", ($tempo_extra / $tarefas_extra)),
      'media_tempo' => gmdate("H:i:s", ($tempoTotal / count($tarefas))),

      'alteracao_planejamento_itweb' => $alteracao_planejamento_itweb,
      'alteracao_planejamento_cliente' => $alteracao_planejamento_cliente,
      'alteracao_extra_itweb' => $alteracao_extra_itweb,
      'alteracao_extra_cliente' => $alteracao_extra_cliente,
      'alteracao_total' => $alteracao_total
    ];

    return self::responseJsonNovo($response, $result);
  }

  /**
   * @param Request $request
   * @param Response $response
   * @param HorasRepository $repoHoras
   * @param ClienteRepository $repoCliente
   * @param $data
   * @return Response
   * @throws \Exception
   */
  public function quantidade(Request $request, Response $response, HorasRepository $repoHoras, ClienteRepository $repoCliente, $data): Response
  {
    $result = [];
    $clientes = $repoCliente->getRepository()->findBy(['status' => 1], ['razao_social' => 'ASC'], 5000)->itensToArray();
    $tarefas = $repoHoras->getRepository()->findBy([], ['data' => 'ASC'], 15000, 1, ['busca' => $data, 'campos_busca' => ['data']])->itensToArray();

    /** @var ClienteEntity $cliente */
    foreach ($clientes as $cliente) {
      $tarefas_planejamento = 0;
      $tarefas_extra = 0;

      /** @var HorasEntity $tarefa */
      foreach ($tarefas as $tarefa) {
        if ($tarefa->cliente_uuid == $cliente->uuid) {

          if ($tarefa->planejamento) {
            $tarefas_planejamento++;
          } else {
            $tarefas_extra++;
          }
        }
      }

      if (($tarefas_planejamento + $tarefas_extra) > 0) {
        array_push($result, [
          'uuid' => $cliente->uuid,
          'razao_social' => $cliente->razao_social,
          'qtd_posts' => $cliente->posts,
          'tarefas_planejamento' => $tarefas_planejamento,
          'tarefas_extra' => $tarefas_extra,
        ]);
      }
    }

    return self::responseJsonNovo($response, $result);
  }

  public function tempo(Request $request, Response $response, HorasRepository $repoHoras, ClienteRepository $repoCliente, PassoRepository $repoPasso, $data): Response
  {
    $result = [];
    $clientes = $repoCliente->getRepository()->findBy(['status' => 1], ['razao_social' => 'ASC'], 5000)->itensToArray();
    $tarefas = $repoHoras->getRepository()->findBy([], ['data' => 'ASC'], 15000, 1, ['busca' => $data, 'campos_busca' => ['data']])->itensToArray();

    /** @var ClienteEntity $cliente */
    foreach ($clientes as $cliente) {
      $tarefas_planejamento = 0;
      $tarefas_extra = 0;
      $tempo_planejamento = 0;
      $tempo_extra = 0;
      $tempoTotal = 0;

      /** @var HorasEntity $tarefa */
      foreach ($tarefas as $tarefa) {
        if ($tarefa->cliente_uuid == $cliente->uuid) {
          $passos = $repoPasso->getRepository()->findBy(['tarefa_uuid' => $tarefa->uuid], ['criado' => 'ASC'], 5000)->itensToArray();
          $tempo = 0;

          for ($i = 0; $i < count($passos); $i++) {
            if (str_contains($passos[$i]->titulo, ' > EM EXECUÇÃO')) {
              $inicio = $passos[$i]->criado->object;
              $fim = $passos[$i + 1]->criado->object;
              $tempo += $this->calcularTempo($inicio, $fim);
            }
          }

          $tempoTotal += $tempo;
          if ($tarefa->planejamento) {
            $tarefas_planejamento++;
            $tempo_planejamento += $tempo;
          } else {
            $tarefas_extra++;
            $tempo_extra += $tempo;
          }
        }
      }

      if (($tarefas_planejamento + $tarefas_extra) > 0) {
        array_push($result, [
          'uuid' => $cliente->uuid,
          'razao_social' => $cliente->razao_social,

          'tempo_planejamento' => gmdate("H:i:s", $tarefas_planejamento > 0 ? ($tempo_planejamento / $tarefas_planejamento) : 0),
          'tempo_extra' => gmdate("H:i:s", $tarefas_extra > 0 ? ($tempo_extra / $tarefas_extra) : 0),
          'media_tempo' => gmdate("H:i:s", ($tempoTotal / ($tarefas_planejamento + $tarefas_extra))),
        ]);
      }
    }

    return self::responseJsonNovo($response, $result);
  }

  public function alteracao(Request $request, Response $response, HorasRepository $repoHoras, ClienteRepository $repoCliente, PassoRepository $repoPasso, $data): Response
  {
    $result = [];
    $clientes = $repoCliente->getRepository()->findBy(['status' => 1], ['razao_social' => 'ASC'], 5000)->itensToArray();
    $tarefas = $repoHoras->getRepository()->findBy([], ['data' => 'ASC'], 15000, 1, ['busca' => $data, 'campos_busca' => ['data']])->itensToArray();

    /** @var ClienteEntity $cliente */
    foreach ($clientes as $cliente) {
      $alteracao_planejamento_itweb = 0;
      $alteracao_planejamento_cliente = 0;
      $alteracao_extra_itweb = 0;
      $alteracao_extra_cliente = 0;
      $alteracao_total = 0;

      /** @var HorasEntity $tarefa */
      foreach ($tarefas as $tarefa) {
        if ($tarefa->cliente_uuid == $cliente->uuid) {
          $passos = $repoPasso->getRepository()->findBy(['tarefa_uuid' => $tarefa->uuid], ['criado' => 'ASC'], 5000)->itensToArray();
          $alteracao_itweb = 0;
          $alteracao_cliente = 0;

          for ($i = 0; $i < count($passos); $i++) {
            if (str_contains($passos[$i]->titulo, 'AGUARDANDO CLIENTE > EM FILA')) {
              $alteracao_cliente++;
            }
            if (str_contains($passos[$i]->titulo, 'AGUARDANDO REVISÃO > EM EXECUÇÃO')) {
              $alteracao_itweb++;
            }
          }

          if ($tarefa->planejamento) {
            $alteracao_planejamento_itweb += $alteracao_itweb;
            $alteracao_planejamento_cliente += $alteracao_cliente;
          } else {
            $alteracao_extra_itweb += $alteracao_itweb;
            $alteracao_extra_cliente += $alteracao_cliente;
          }
        }
      }

      array_push($result, [
        'uuid' => $cliente->uuid,
        'razao_social' => $cliente->razao_social,

        'alteracao_planejamento_itweb' => $alteracao_planejamento_itweb,
        'alteracao_planejamento_cliente' => $alteracao_planejamento_cliente,
        'alteracao_extra_itweb' => $alteracao_extra_itweb,
        'alteracao_extra_cliente' => $alteracao_extra_cliente,
        'alteracao_total' => $alteracao_total
      ]);
    }

    return self::responseJsonNovo($response, $result);
  }

  /**
   * @param Request $request
   * @param Response $response
   * @param $data
   * @param $cliente_uuid
   * @param null $designer_uuid
   * @param HorasRepository $repoHoras
   * @param PassoRepository $repoPasso
   * @return Response
   * @throws \Exception
   */
  public function intervalo(Request $request, Response $response, HorasRepository $repoHoras, PassoRepository $repoPasso, $data_inicio, $data_fim, $cliente_uuid, $designer_uuid = null): Response
  {
    $resultParcial = [];
    if ($designer_uuid) {
      $tarefas = $repoHoras->getRepository()->findBy(['designer_uuid' => $designer_uuid, 'cliente_uuid' => $cliente_uuid], ['data' => 'ASC'], 5000, 1, ['busca' => $data, 'campos_busca' => ['data']])->itensToArray();
    } else {
      $tarefas = $repoHoras->getRepository()->findBy(['cliente_uuid' => $cliente_uuid], ['data' => 'ASC'], 5000, 1, ['busca' => $data, 'campos_busca' => ['data']])->itensToArray();
    }

    $tarefas_planejamento = 0;
    $tarefas_extra = 0;

    $tempo_planejamento = 0;
    $tempo_extra = 0;
    $tempoTotal = 0;

    $alteracao_planejamento_itweb = 0;
    $alteracao_planejamento_cliente = 0;
    $alteracao_extra_itweb = 0;
    $alteracao_extra_cliente = 0;
    $alteracao_total = 0;

    /** @var HorasEntity $tarefa */
    foreach ($tarefas as $tarefa) {
      $passos = $repoPasso->getRepository()->findBy(['tarefa_uuid' => $tarefa->uuid], ['criado' => 'ASC'], 5000)->itensToArray();
      $tempo = 0;
      $alteracao_itweb = 0;
      $alteracao_cliente = 0;
      for ($i = 0; $i < count($passos); $i++) {
        if (str_contains($passos[$i]->titulo, ' > EM EXECUÇÃO')) {
          $inicio = $passos[$i]->criado->object;
          $fim = $passos[$i + 1]->criado->object;
          $tempo += $this->calcularTempo($inicio, $fim);
        }
        if (str_contains($passos[$i]->titulo, 'AGUARDANDO CLIENTE > EM FILA')) {
          $alteracao_cliente++;
        }
        if (str_contains($passos[$i]->titulo, 'AGUARDANDO REVISÃO > EM EXECUÇÃO')) {
          $alteracao_itweb++;
        }
      }
      if ($tarefa->planejamento) {
        $tarefas_planejamento++;
        $tempo_planejamento += $tempo;
        $alteracao_planejamento_itweb += $alteracao_itweb;
        $alteracao_planejamento_cliente += $alteracao_cliente;
      } else {
        $tarefas_extra++;
        $tempo_extra += $tempo;
        $alteracao_extra_itweb += $alteracao_itweb;
        $alteracao_extra_cliente += $alteracao_cliente;
      }

      $tempoTotal += $tempo;
      $alteracao_total += ($alteracao_cliente + $alteracao_itweb);
      array_push($resultParcial, [
        'tema' => $tarefa->tema,
        'data' => $tarefa->data,
        'planejamento' => $tarefa->planejamento,
        'tempo' => gmdate("H:i:s", $tempo),
        'alteracao_interna' => $alteracao_itweb,
        'alteracao_externa' => $alteracao_cliente
      ]);
    }

    $result = [
      'designer_uuid' => $tarefas[0]->designer_uuid,
      'cliente_uuid' => $tarefas[0]->cliente_uuid,
      'designer' => $tarefas[0]->nome,
      'cliente' => $tarefas[0]->razao_social,

      'qtd_posts' => $tarefas[0]->qtd_posts,
      'tarefas' => $resultParcial,
      'tarefas_planejamento' => $tarefas_planejamento,
      'tarefas_extra' => $tarefas_extra,

      'tempo_planejamento' => gmdate("H:i:s", ($tempo_planejamento / $tarefas_planejamento)),
      'tempo_extra' => gmdate("H:i:s", ($tempo_extra / $tarefas_extra)),
      'media_tempo' => gmdate("H:i:s", ($tempoTotal / count($tarefas))),

      'alteracao_planejamento_itweb' => $alteracao_planejamento_itweb,
      'alteracao_planejamento_cliente' => $alteracao_planejamento_cliente,
      'alteracao_extra_itweb' => $alteracao_extra_itweb,
      'alteracao_extra_cliente' => $alteracao_extra_cliente,
      'alteracao_total' => $alteracao_total
    ];

    return self::responseJsonNovo($response, $result);
  }

  private function calcularTempo(DateTime $inicio, DateTime $fim): int
  {
    if ($inicio->format('d') == $fim->format('d')) { // Se for no mesmo dia
      if (!($inicio->format('H') < 12 && $fim->format('H') > 13)) { // Se não houver pausa do almoço
        $diferenca = $fim->diff($inicio);
        $minutos = ($diferenca->h * 60) + $diferenca->i;
        return ($minutos * 60) + $diferenca->s;
      } else {
        $diferenca = $fim->diff($inicio);
        $minutos = (($diferenca->h - 1) * 60) + $diferenca->i;
        return ($minutos * 60) + $diferenca->s;
      }
    } else {
      return $this->calcularTempo($inicio, $fim->sub(date_interval_create_from_date_string('16 hours')));
    }
  }
}
