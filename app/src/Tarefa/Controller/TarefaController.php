<?php

namespace Modulo\Tarefa\Controller;

use Boot\Provider\Jwt\JwtController;
use Core\Controller\BaseController;
use Doctrine\DBAL\Exception;
use Modulo\Cliente\Entity\ClienteEntity;
use Modulo\Cliente\Repository\ClienteRepository;
use Modulo\Passo\Repository\PassoRepository;
use Modulo\Post\Entity\PostEntity;
use Modulo\Post\Repository\PostRepository;
use Modulo\Tarefa\Entity\TarefaEntity;
use Modulo\Tarefa\Repository\TarefaRepository;
use Modulo\TipoTarefa\Entity\TipoTarefaEntity;
use Modulo\TipoTarefa\Repository\TipoTarefaRepository;
use Modulo\User\Entity\UserEntity;
use Modulo\User\Repository\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;
use function PHPUnit\Framework\isNull;

class TarefaController extends BaseController
{
  /** @var TarefaRepository */
  public $tarefaRepository;
  /** @var JwtController */
  public $jwt;

  public function __construct(TarefaRepository $tarefaRepository, JwtController $jwtController)
  {
    $this->tarefaRepository = $tarefaRepository;
    $this->jwt = $jwtController;
  }

  private function isBissexto(int $ano): bool
  {
    return (($ano % 4 == 0 && $ano % 100 != 0) || $ano % 400 == 0);
  }

  private function numberToString(int $numero): string
  {
    if ($numero < 10) {
      return ('0' . $numero);
    } else {
      return ((string)$numero);
    }
  }

  /**
   * @throws Exception
   */
  private function calculoData(string $data, int $valor): string
  {
    $dataSeparada = explode("-", $data);
    if (count($dataSeparada) != 3) {
      throw new Exception("Formato de data inválido");
    }
    $ano = (int)$dataSeparada[0];
    $mes = (int)$dataSeparada[1];
    $dia = (int)$dataSeparada[2];

    $isBissexto = $this->isBissexto($ano);

    if (($dia + $valor) < 1) {
      if (($mes - 1) > 0) {
        if (($mes - 1) == 2) {
          if ($isBissexto) {
            return ($ano . '-02-' . $this->numberToString(30 + $valor));
          } else {
            return ($ano . '-02-' . $this->numberToString(29 + $valor));
          }
        } else {
          if (($mes - 1) == 1 || ($mes - 1) == 3 || ($mes - 1) == 5 || ($mes - 1) == 7 || ($mes - 1) == 8 || ($mes - 1) == 10 || ($mes - 1) == 12) {
            return ($ano . '-' . $this->numberToString($mes - 1) . '-' . $this->numberToString(32 + $valor));
          } else {
            return ($ano . '-' . $this->numberToString($mes - 1) . '-' . $this->numberToString(31 + $valor));
          }
        }
      } else {
        return (($ano - 1) . '-12-' . $this->numberToString(31 + $valor));
      }
    } else {
      if ($mes == 12) {
        if (($dia + $valor) > 31) {
          return (($ano + 1) . '-01-' . $this->numberToString($dia + $valor - 31));
        } else {
          return ($ano . '-12-' . $this->numberToString($dia + $valor));
        }
      } elseif ($mes == 2) {
        if ($isBissexto && ($dia + $valor) > 29) {
          return ($ano . '-' . $this->numberToString($mes + 1) . '-' . $this->numberToString($dia + $valor - 29));
        } elseif (($dia + $valor) > 28) {
          return ($ano . '-' . $this->numberToString($mes + 1) . '-' . $this->numberToString($dia + $valor - 28));
        } else {
          return ($ano . '-02-' . $this->numberToString($dia + $valor));
        }
      } elseif ($mes == 1 || $mes == 3 || $mes == 5 || $mes == 7 || $mes == 8 || $mes == 10) {
        if (($dia + $valor) > 31) {
          return ($ano . '-' . $this->numberToString($mes + 1) . '-' . $this->numberToString(($dia + $valor - 31)));
        } else {
          return ($ano . '-' . $this->numberToString($mes) . '-' . $this->numberToString($dia + $valor));
        }
      } else {
        if (($dia + $valor) > 30) {
          return ($ano . '-' . $this->numberToString($mes + 1) . '-' . $this->numberToString(($dia + $valor - 30)));
        } else {
          return ($ano . '-' . $this->numberToString($mes) . '-' . $this->numberToString($dia + $valor));
        }
      }
    }
  }

  /**
   * @param $data
   * @param $nivel
   * @param $colaborador_uuid
   * @param $setor
   * @param Request $request
   * @param Response $response
   * @param ClienteRepository $repoCliente
   * @param UserRepository $userRepository
   * @param TipoTarefaRepository $tipoTarefaRepository
   * @return Response
   * @throws Exception
   * @throws HttpBadRequestException
   * @throws \Exception
   */
  public function findAll($data, $nivel, $setor, Request $request, Response $response, ClienteRepository $repoCliente, UserRepository $userRepository, TipoTarefaRepository $tipoTarefaRepository, $colaborador_uuid = null): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'designer', 'atendimento', 'qa', 'desenvolvimento', 'fotografia'])) {
      $argumentos = $request->getParsedBody();
      $dataBusca = explode("-", $data);
      $dataBusca = $dataBusca[0] . "-" . $dataBusca[1] . '-';

      $datas = [];
      for ($i = -10; $i < 10; $i++) {
        array_push($datas, $this->calculoData($data, $i));
      }

      switch ($nivel) {
        case 'admin':
        case 'sadmin':
        case 'atendimento':
        case 'qa':
          if ($colaborador_uuid) {
            $where['user_uuid'] = $colaborador_uuid;
          }
          $where['setor'] = $setor;
          $tarefas = [];
          for ($i = 0; $i < count($datas); $i++) {
            $where['data'] = $datas[$i];
            foreach ($this->tarefaRepository->getRepository()->findBy(array_filter($where), ['data' => 'ASC'], 5000)->getItens() as $item) {
              array_push($tarefas, $item);
            }
          }

          foreach ($this->tarefaRepository->findAtrasados($setor, $data, $colaborador_uuid ? $colaborador_uuid : null)->getItens() as $item) {
            array_push($tarefas, $item);
          }

          $tarefas = array_unique($tarefas, SORT_REGULAR);

          usort($tarefas, function (TarefaEntity $a, TarefaEntity $b) {
            if ($a->data->value() == $b->data->value()) return 0;
            return (($a->data->value() < $b->data->value()) ? -1 : 1);
          });
          break;
        case 'planner':
        case 'colaborador':
          $userInfo = $this->jwt->decodeNovo($request);

          $tarefas = [];
          for ($i = 0; $i < count($datas); $i++) {
            foreach ($this->tarefaRepository->getRepository()->findBy(['setor' => $setor, 'data' => $datas[$i], 'user_uuid' => $userInfo->uuid], ['data' => 'ASC'], 5000)->getItens() as $item) {
              array_push($tarefas, $item);
            }
          }

          foreach ($this->tarefaRepository->findAtrasados($setor, $data, $userInfo->uuid)->getItens() as $item) {
            array_push($tarefas, $item);
          }

          $tarefas = array_unique($tarefas, SORT_REGULAR);

          usort($tarefas, function (TarefaEntity $a, TarefaEntity $b) {
            if ($a->data->value() == $b->data->value()) return 0;
            return (($a->data->value() < $b->data->value()) ? -1 : 1);
          });
          break;
        default:
          $tarefas = [];
      }

      $itens = array_reduce($tarefas, function ($result, $tarefa) use (&$repoCliente, &$userRepository, &$tipoTarefaRepository) {
        /** @var TarefaEntity $tarefa */
        /** @var ClienteEntity $cliente */
        $cliente = $repoCliente->getRepository()->find($tarefa->cliente_uuid);
        /** @var UserEntity $colaborador */
        $colaborador = $userRepository->getRepository()->find($tarefa->user_uuid);
        if ($cliente && $colaborador) {
          $result[] = [
            'uuid' => $tarefa->uuid->value(),
            'cliente' => $cliente->razao_social->value(),
            'tema' => $tarefa->tema->value(),
            'colaborador' => ['uuid' => $colaborador->uuid->value(), 'nome' => $colaborador->nome->value(), 'foto' => $colaborador->foto->value()],
            'post' => !is_null($tarefa->post_uuid->value()) ? ['uuid' => $tarefa->post_uuid->value()] : null,
            'data' => $tarefa->data->value(),
            'prioridade' => $tarefa->prioridade->value(),
            'status' => $tarefa->status->value(),
            'isAlteracao' => (str_contains($tarefa->log->value(), 'Alterou status: revisao => execucao') || str_contains($tarefa->log->value(), 'Alterou status: cliente => fila'))
          ];
        }
        return $result;
      }, []);

    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }

    return self::responseJson($response, $itens, 200);
  }

  /**
   * @throws \Exception
   */
  public function atualizarTarefasByCliente($uuidCliente, $uuidColaboradorAntigo, $uuidColaboradorNovo)
  {
    try {
      $this->tarefaRepository->getRepository()->update(['cliente_uuid' => $uuidCliente], ['user_uuid' => $uuidColaboradorNovo]);
    } catch (\Exception $exception) {
      throw new \Exception($exception);
    }
  }

  public function script01(Request $request, Response $response, ClienteRepository $clienteRepository)
  {
    try {
      $clientes = $clienteRepository->getRepository()->findBy(['status' => 1], [], 5000)->getItens();
      /** @var ClienteEntity $cliente */
      foreach ($clientes as $cliente) {
        $this->tarefaRepository->getRepository()->update(['cliente_uuid' => $cliente->uuid->value()], ['user_uuid' => $cliente->usuario_uuid->value()]);
      }
    } catch (\Exception $exception) {
      throw new \Exception($exception);
    }
  }

  /**
   * @param Request $request
   * @param Response $response
   * @param PostRepository $postRepository
   * @param ClienteRepository $clienteRepository
   * @return Response
   * @throws Exception
   * @throws HttpBadRequestException
   * @throws HttpNotFoundException
   * @throws \Exception
   */
  public function create(Request $request, Response $response, PostRepository $postRepository, ClienteRepository $clienteRepository): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'atendimento', 'designer', 'desenvolvimento', 'fotografia'])) {
      $argumentos = $request->getParsedBody();
      $userInfo = $this->jwt->decodeNovo($request);

      /** @var TarefaEntity $entity */
      $entity = $this->tarefaRepository->getRepository()::getEntity();
      $entity->hydrator(['status' => 'fila']);

      if ($argumentos['post_uuid']) {
        /** @var PostEntity $post */
        $post = $postRepository->getRepository()->find($argumentos['post_uuid']);
        if ($post) {
          /** @var ClienteEntity $cliente */
          $cliente = $clienteRepository->getRepository()->find($post->cliente_uuid->value());
          if ($cliente) {
            $entity->hydrator([
              'cliente_uuid' => $post->cliente_uuid->value(),
              'user_uuid' => $cliente->usuario_uuid->value(),
              'tipo_uuid' => $post->tipo_uuid->value(),
              'data' => $post->data->value(),
              'tema' => $post->tema->value(),
//              'informacao' => '<p>Sugestão de imagem:</p><p>' . $post->sugestao . '</p><br><p>Texto:</p><p>' . $post->texto . '</p><br><p>Legenda:</p><p>' . $post->legenda . '</p>'
            ]);
          }
        }
      }

      $entity->log->add(['nome' => $userInfo->nome, 'uuid' => $userInfo->uuid], 'Criou tarefa');

      if ($argumentos['status']) {
        $entity->log->add(['nome' => $userInfo->nome, 'uuid' => $userInfo->uuid], 'Definiu status: ' . $argumentos['status']);
      } else {
        $entity->log->add(['nome' => $userInfo->nome, 'uuid' => $userInfo->uuid], 'Definiu status: pendente');
      }

      $entity->hydrator($argumentos);

      if ($entity->setor->value() == null || $entity->setor->value() == '') {
        throw new \Exception("Não foi possível salvar o registro.");
      }
      if ($entity->cliente_uuid->value() == '') {
        throw new \Exception("Não foi possível salvar o registro.");
      }
      if ($entity->tema->value() == null || $entity->tema->value() == '') {
        throw new \Exception("Não foi possível salvar o registro.");
      }
      if ($entity->user_uuid->value() == '') {
        throw new \Exception("Não foi possível salvar o registro.");
      }
      if ($entity->tipo_uuid->value() == '') {
        $entity->hydrator(['tipo_uuid' => 'ed1973fe-575e-4b66-923e-676ed0ab5430']);
//        throw new \Exception("Não foi possível salvar o registro.");
      }

      try {
        $novoRegistroId = $this->tarefaRepository->getRepository()->insert($entity->toArray());
      } catch (\Exception $e) {
        throw new \Exception("Não foi possivel salvar");
      }

      if ($novoRegistroId) {
        return self::responseJson($response, $novoRegistroId);
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
   * @param PostRepository $postRepository
   * @param ClienteRepository $clienteRepository
   * @param $uuid
   * @return Response
   * @throws Exception
   * @throws HttpBadRequestException
   * @throws HttpNotFoundException
   * @throws \Exception
   */
  public function update(Request $request, Response $response, PostRepository $postRepository, ClienteRepository $clienteRepository, $uuid): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'atendimento', 'designer', 'desenvolvimento', 'fotografia'])) {
      $argumentos = $request->getParsedBody();
      $userInfo = $this->jwt->decodeNovo($request);

      /** @var TarefaEntity $entity */
      $entity = $this->tarefaRepository->getRepository()->find($uuid);
      if (empty($entity)) {
        throw new HttpNotFoundException($request, "Registro inválido;");
      }
      $status_old = $entity->status->value();

      $entity->hydrator($argumentos);

      if (!empty($status_old) && ($status_old != $argumentos['status'])) {
        $entity->log->add(['nome' => $userInfo->nome, 'uuid' => $userInfo->uuid], 'Alterou status: ' . $status_old . ' => ' . $argumentos['status']);

        if ($argumentos['status'] == 'concluida' && $entity->post_uuid->value()) {
          /** @var PostEntity $post */
          $post = $postRepository->getRepository()->find($entity->post_uuid->value());
          if ($post) {
            $post->feito->set(true);
            $postRepository->getRepository()->update(['uuid' => $post->uuid->value()], $post->toSave());
          }
        }

        if ($status_old == 'concluida' && $entity->post_uuid->value()) {
          /** @var PostEntity $post */
          $post = $postRepository->getRepository()->find($entity->post_uuid->value());
          if ($post) {
            $post->feito->set(false);
            $postRepository->getRepository()->update(['uuid' => $post->uuid->value()], $post->toSave());
          }
        }
      }

      if ($entity->setor->value() == null || $entity->setor->value() == '') {
        throw new \Exception("Não foi possível salvar o registro.");
      }
      if ($entity->cliente_uuid->value() == '') {
        throw new \Exception("Não foi possível salvar o registro.");
      }
      if ($entity->tema->value() == null || $entity->tema->value() == '') {
        throw new \Exception("Não foi possível salvar o registro.");
      }
      if ($entity->user_uuid->value() == '') {
        throw new \Exception("Não foi possível salvar o registro.");
      }
      if ($entity->tipo_uuid->value() == '') {
        $entity->hydrator(['tipo_uuid' => 'ed1973fe-575e-4b66-923e-676ed0ab5430']);
//        throw new \Exception("Não foi possível salvar o registro.");
        //TODO VERIFICAR O FUNCIONAMENTO DE TIPOS
      }

      $registroEditado = $this->tarefaRepository->getRepository()->update(['uuid' => $entity->uuid->value()], $entity->toArray());
      if ($registroEditado) {
        return self::responseJson($response, $entity->toApi());
      } else {
        throw new \Exception("Não foi possível editar o registro.");
      }
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }
  }

  /**
   * @throws HttpBadRequestException
   * @throws Exception
   * @throws \Exception
   */
  public function saveByPlanejamento(Request $request, Response $response, PostRepository $postRepository, ClienteRepository $clienteRepository): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'atendimento'])) {
      $argumentos = $request->getParsedBody();
      $cliente_uuid = $argumentos['cliente_uuid'];
      $mes = $argumentos['mes'];
      $ano = $argumentos['ano'];
      $userInfo = $this->jwt->decodeNovo($request);

      if (!$cliente_uuid || !$mes || !$ano) {
        throw new \Exception("Argumento não informado");
      }

      switch ($mes) {
        case 'Janeiro':
          $dataBusca = $ano . '-01-';
          break;
        case 'Fevereiro':
          $dataBusca = $ano . '-02-';
          break;
        case 'Março':
          $dataBusca = $ano . '-03-';
          break;
        case 'Abril':
          $dataBusca = $ano . '-04-';
          break;
        case 'Maio':
          $dataBusca = $ano . '-05-';
          break;
        case 'Junho':
          $dataBusca = $ano . '-06-';
          break;
        case 'Julho':
          $dataBusca = $ano . '-07-';
          break;
        case 'Agosto':
          $dataBusca = $ano . '-08-';
          break;
        case 'Setembro':
          $dataBusca = $ano . '-09-';
          break;
        case 'Outubro':
          $dataBusca = $ano . '-10-';
          break;
        case 'Novembro':
          $dataBusca = $ano . '-11-';
          break;
        case 'Dezembro':
          $dataBusca = $ano . '-12-';
          break;
        default:
          $dataBusca = '';
      }

      $posts = $postRepository->getRepository()->findBy(['cliente_uuid' => $cliente_uuid], ['data' => 'ASC'], 5000, 1, ['busca' => $dataBusca, 'campos_busca' => ['data']])->getItens();
      $countAdicionados = 0;

      /** @var PostEntity $post */
      foreach ($posts as $post) {
        /** @var ClienteEntity $cliente */
        $cliente = $clienteRepository->getRepository()->find($post->cliente_uuid->value());
        /** @var TarefaEntity $entity */
        $tarefaExistente = $this->tarefaRepository->getRepository()->find($post->uuid->value(), 'post_uuid');
        if ($tarefaExistente) {
          $entity = $tarefaExistente;

          $entity->hydrator([
            'data' => $post->data->value(),
            'tema' => $post->tema->value(),
            'tipo_uuid' => $post->tipo_uuid->value(),
//            'informacao' => '<p>Sugestão de imagem:</p><p>' . $post->sugestao . '</p><br><p>Texto:</p><p>' . $post->texto . '</p><br><p>Legenda:</p><p>' . $post->legenda . '</p>'
          ]);

          $entity->log->add(['nome' => $userInfo->nome, 'uuid' => $userInfo->uuid], 'Alterou a tarefa pelo planejamento');

          try {
            $this->tarefaRepository->getRepository()->update(['uuid' => $entity->uuid->value()], $entity->toArray());
            $countAdicionados++;
          } catch (\Exception $e) {
            throw new \Exception("Não foi possivel salvar");
          }
        } else {
          $entity = $this->tarefaRepository->getRepository()::getEntity();

          if ($cliente) {
            $entity->hydrator([
              'cliente_uuid' => $post->cliente_uuid->value(),
              'user_uuid' => $cliente->usuario_uuid->value(),
              'post_uuid' => $post->uuid->value(),
              'tipo_uuid' => $post->tipo_uuid->value(),
              'data' => $post->data->value(),
              'tema' => $post->tema->value(),
//              'informacao' => '<p>Sugestão de imagem:</p><p>' . $post->sugestao . '</p><br><p>Texto:</p><p>' . $post->texto . '</p><br><p>Legenda:</p><p>' . $post->legenda . '</p>',
              'prioridade' => 'baixa',
              'status' => 'fila',
              'setor' => 'criacao'
            ]);

            $entity->log->add(['nome' => $userInfo->nome, 'uuid' => $userInfo->uuid], 'Criou tarefa');
            $entity->log->add(['nome' => $userInfo->nome, 'uuid' => $userInfo->uuid], 'Definiu status: fila');

            try {
              $this->tarefaRepository->getRepository()->insert($entity->toArray());
              $countAdicionados++;
            } catch (\Exception $e) {
              throw new \Exception("Não foi possivel salvar");
            }
          } else {
            throw new \Exception("Cliente não encontrado");
          }
        }
      }
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }

    return self::responseJson($response, "Adicionados " . $countAdicionados . " de " . count($posts));
  }

  /**
   * @param Request $request
   * @param Response $response
   * @param ClienteRepository $repoCliente
   * @param UserRepository $userRepository
   * @param TipoTarefaRepository $tipoTarefaRepository
   * @param PostRepository $postRepository
   * @param $uuid
   * @return Response
   * @throws Exception
   * @throws HttpBadRequestException
   */
  public function find(Request $request, Response $response, ClienteRepository $repoCliente, UserRepository $userRepository, TipoTarefaRepository $tipoTarefaRepository, PostRepository $postRepository, $uuid): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'designer', 'desenvolvimento', 'fotografia'])) {
      /** @var TarefaEntity $tarefa */
      $tarefa = $this->tarefaRepository->getRepository()->find($uuid);
      $item = [];

      if ($tarefa) {
        $item = [
          'uuid' => $tarefa->uuid->value(),
          'tema' => $tarefa->tema->value(),
          'setor' => $tarefa->setor->value(),
          'data' => $tarefa->data->value(),
          'informacao' => $tarefa->informacao->value(),
          'prioridade' => ['uuid' => $tarefa->prioridade->value(), 'nome' => ($tarefa->prioridade->value() == 'alta' || $tarefa->prioridade->value() == 'baixa') ? ucfirst($tarefa->prioridade->value()) : "Média"],
          'status' => $tarefa->status->value(),
        ];

        /** @var ClienteEntity $cliente */
        $cliente = $repoCliente->getRepository()->find($tarefa->cliente_uuid);

        if ($cliente) {
          $item['cliente'] = [
            'uuid' => $cliente->uuid->value(),
            'razao_social' => $cliente->razao_social->value()
          ];
          $item['assinatura'] = $cliente->assinatura->value() ?: '';
        }

        /** @var UserEntity $designer */
        $designer = $userRepository->getRepository()->find($tarefa->user_uuid);

        if ($designer) {
          $item['designer'] = [
            'uuid' => $designer->uuid->value(),
            'nome' => $designer->nome->value(),
            'foto' => $designer->foto->value(),
            'nivel' => $designer->nivel->value()
          ];
        }

        /** @var TipoTarefaEntity $tipo */
        $tipo = $tipoTarefaRepository->getRepository()->find($tarefa->tipo_uuid);

        if ($tipo) {
          $item['tipo'] = [
            'uuid' => $tipo->uuid->value(),
            'nome' => $tipo->nome->value(),
            'pontos' => $tipo->pontos->value()
          ];

          if ($tarefa->post_uuid->value()) {
            /** @var PostEntity $post */
            $post = $postRepository->getRepository()->find($tarefa->post_uuid->value());
            if ($post) {
              $item['post'] = [
                'sugestao' => $post->sugestao->value(),
                'texto' => $post->texto->value(),
                'legenda' => $post->legenda->value()
              ];
            }
          }
        }
      }
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }
    return self::responseJson($response, $item);
  }
}
