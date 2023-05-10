<?php

namespace Modulo\Post\Controller;

use Boot\Provider\Jwt\JwtController;
use Core\Lib\Acl\AclCheck;
use Core\Request\RequestApi;
use Core\Controller\BaseController;
use Doctrine\DBAL\Exception;
use HttpException;
use Modulo\Cliente\Entity\ClienteEntity;
use Modulo\Cliente\Repository\ClienteRepository;
use Modulo\Post\Entity\PostEntity;
use Modulo\Post\Repository\PostRepository;
use Core\Controller\Lib\ControllerView;
use Modulo\Post\Request\PostRequestApi;
use Modulo\Tarefa\Entity\TarefaEntity;
use Modulo\Tarefa\Repository\TarefaRepository;
use Modulo\TipoTarefa\Repository\TipoTarefaRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;
use Modulo\Post\Provider\PostAclProvider;

class PostController extends BaseController
{
  public ControllerView $view;
  public RequestApi $api;
  public PostRepository $postRepository;
  public AclCheck $acl;
  public JwtController $jwt;

  public function __construct(
    PostAclProvider $postAclProvider,
    PostRepository  $postRepository,
    PostRequestApi  $postRequestApi,
    JwtController   $jwtController
  )
  {
    $this->api = $postRequestApi->getApi();
    $this->postRepository = $postRepository;
    $this->acl = $postAclProvider->getAcl();
    $this->jwt = $jwtController;
  }

  /**
   * @param Request $request
   * @param Response $response
   * @param ClienteRepository $repoCliente
   * @param TipoTarefaRepository $tipoTarefaRepository
   * @param $uuid
   * @return Response
   * @throws Exception
   * @throws HttpBadRequestException
   * @throws HttpNotFoundException
   * @throws \Exception
   */
  public function find(Request $request, Response $response, ClienteRepository $repoCliente, TipoTarefaRepository $tipoTarefaRepository, $uuid): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'fotografia'])) {
      /** @var PostEntity $postEntity */
      $postEntity = $this->postRepository->getRepository()->find($uuid);

      if (empty($postEntity)) {
        throw new HttpNotFoundException($request, "Registro inválido");
      } else {
        /** @var ClienteEntity $cliente */
        $cliente = $repoCliente->getRepository()->findBy(['uuid' => $postEntity->cliente_uuid->value()])->getItem();
        if ($cliente) {
          $tipo_tarefa = $tipoTarefaRepository->getRepository()->findBy(['uuid' => $postEntity->tipo_uuid->value()])->getItem();
          if ($tipo_tarefa) {
            $tempArr = [
              'uuid' => $postEntity->uuid->value(),
              'cliente_uuid' => $postEntity->cliente_uuid->value(),
              'cliente_nome' => $cliente->razao_social->value(),
              'tipo_uuid' => $tipo_tarefa->uuid->value(),
              'tipo_nome' => $tipo_tarefa->nome->value(),
              'tipo_pontos' => $tipo_tarefa->pontos->value(),
              'tema' => $postEntity->tema->value(),
              'data' => $postEntity->data->value(),
              'sugestao' => $postEntity->sugestao->__toString(),
              'texto' => $postEntity->texto->__toString(),
              'legenda' => $postEntity->legenda->__toString(),
              'status' => $postEntity->status->value()
            ];
          } else {
            throw new HttpNotFoundException($request, "Registro inválido");
          }
        } else {
          throw new HttpNotFoundException($request, "Registro inválido");
        }
      }
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }

    return self::responseJson($response, $tempArr);
  }

  /**
   * @param Request $request
   * @param Response $response
   * @param ClienteRepository $repoCliente
   * @return Response
   * @throws HttpBadRequestException
   */
  public function quantidade(Request $request, Response $response, ClienteRepository $repoCliente): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'designer', 'fotografia'])) {

      $argumentos = $request->getParsedBody();
      $mes = $argumentos['mes'];
      $ano = (new \DateTime('now'))->format('Y');

      switch ($mes) {
        case 'Janeiro':
          $dataBusca = '-01-';
          break;
        case 'Fevereiro':
          $dataBusca = '-02-';
          break;
        case 'Março':
          $dataBusca = '-03-';
          break;
        case 'Abril':
          $dataBusca = '-04-';
          break;
        case 'Maio':
          $dataBusca = '-05-';
          break;
        case 'Junho':
          $dataBusca = '-06-';
          break;
        case 'Julho':
          $dataBusca = '-07-';
          break;
        case 'Agosto':
          $dataBusca = '-08-';
          break;
        case 'Setembro':
          $dataBusca = '-09-';
          break;
        case 'Outubro':
          $dataBusca = '-10-';
          break;
        case 'Novembro':
          $dataBusca = '-11-';
          break;
        case 'Dezembro':
          $dataBusca = '-12-';
          break;
        default:
          $dataBusca = '-0000-';
      }

      $naoFeitos = $this->postRepository->getRepository()->findBy(['feito' => 0], [], 50000, 1, ['busca' => $argumentos['busca'] . ' ' . $ano . $dataBusca, 'campos_busca' => ['data']])->totalDeRegistros();
      $feitos = $this->postRepository->getRepository()->findBy(['feito' => 1], [], 50000, 1, ['busca' => $argumentos['busca'] . ' ' . $ano . $dataBusca, 'campos_busca' => ['data']])->totalDeRegistros();
      $total = $feitos + $naoFeitos;
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }

    return self::responseJson($response, (object)['feitos' => $feitos, 'nao_feitos' => $naoFeitos, 'total' => $total]);
  }

  /**
   * @param Request $request
   * @param Response $response
   * @param TarefaRepository $tarefaRepository
   * @param ClienteRepository $clienteRepository
   * @return Response
   * @throws Exception
   * @throws HttpBadRequestException
   * @throws HttpNotFoundException
   * @throws \Exception
   */
  public function create(Request $request, Response $response, TarefaRepository $tarefaRepository, ClienteRepository $clienteRepository): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'fotografia'])) {
      $argumentos = $request->getParsedBody();

      /** @var PostEntity $entity */
      $entity = $this->postRepository->getRepository()::getEntity();
      $entity->hydrator([
        'status' => true,
        'feito' => false
      ]);
      $entity->hydrator($argumentos);

      if (!$argumentos['cliente_uuid'] || !$argumentos['tema'] || !$argumentos['data']) {
        if (!$argumentos['cliente_uuid']) {
          throw new \Exception("Informe o Cliente");
        } elseif (!$argumentos['tema']) {
          throw new \Exception("Informe o Tema");
        } else {
          throw new \Exception("Informe a Data");
        }
      }

      try {
        $novoRegistroId = $this->postRepository->getRepository()->insert($entity->toSave('novo'));
      } catch (\Exception $e) {
        throw new \Exception("Não foi possivel salvar");
      }

      if ($novoRegistroId) {
        $novosDados = $this->postRepository->getRepository()->find($novoRegistroId, 'id');

        $userInfo = $this->jwt->decodeNovo($request);
        $tarefa = $tarefaRepository->getRepository()::getEntity();

        $cliente = $clienteRepository->getRepository()->find($entity->cliente_uuid);
        if ($cliente) {
          $tarefa->hydrator([
            'cliente_uuid' => $entity->cliente_uuid,
            'user_uuid' => $cliente->usuario_uuid->value(),
            'post_uuid' => $entity->uuid,
            'tipo_uuid' => $entity->tipo_uuid,
            'data' => $entity->data,
            'tema' => $entity->tema,
            'informacao' => '<p>Sugestão de imagem:</p><p>' . $entity->sugestao . '</p><br><p>Texto:</p><p>' . $entity->texto . '</p><br><p>Legenda:</p><p>' . $entity->legenda . '</p>' . $cliente->assinatura ? ('<p>' . $cliente->assinatura . '</p>') : '',
            'prioridade' => 'baixa',
            'status' => 'fila',
            'setor' => 'criacao'
          ]);

          $tarefa->log->add(['nome' => $userInfo->nome, 'uuid' => $userInfo->uuid], 'Criou tarefa');
          $tarefa->log->add(['nome' => $userInfo->nome, 'uuid' => $userInfo->uuid], 'Definiu status: fila');

          try {
            $tarefaRepository->getRepository()->insert($tarefa->toArray());
          } catch (\Exception $e) {
            throw new \Exception("Não foi possivel salvar");
          }
        } else {
          throw new \Exception("Cliente não encontrado");
        }
        return self::responseJson($response, $novosDados);
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
   * @param TarefaRepository $tarefaRepository
   * @param ClienteRepository $clienteRepository
   * @param $uuid
   * @return Response
   * @throws Exception
   * @throws HttpBadRequestException
   * @throws HttpNotFoundException
   */
  public function update(Request $request, Response $response, TarefaRepository $tarefaRepository, ClienteRepository $clienteRepository, $uuid): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner'])) {
      $argumentos = $request->getParsedBody();

      /** @var PostEntity $entity */
      $entity = $this->postRepository->getRepository()->find($uuid);
      if (empty($entity)) {
        throw new HttpNotFoundException($request, "Registro inválido;");
      }

      $entity->hydrator($argumentos);

      $registroEditado = $this->postRepository->getRepository()->update(['uuid' => $entity->uuid->value()], $entity->toSave('editar'));
      if ($registroEditado) {
        $userInfo = $this->jwt->decodeNovo($request);

        /** @var TarefaEntity $tarefa */
        $tarefa = $tarefaRepository->getRepository()->find($entity->uuid, 'post_uuid');
        if ($tarefa) {
          $tarefa->hydrator([
            'data' => $entity->data,
            'tema' => $entity->tema,
            'informacao' => '<p>Sugestão de imagem:</p><p>' . $entity->sugestao . '</p><br><p>Texto:</p><p>' . $entity->texto . '</p><br><p>Legenda:</p><p>' . $entity->legenda . '</p>'
          ]);

          $tarefa->log->add(['nome' => $userInfo->nome, 'uuid' => $userInfo->uuid], 'Alterou a tarefa pelo planejamento');

          try {
            $tarefaRepository->getRepository()->update(['uuid' => $tarefa->uuid->value()], $tarefa->toArray());
          } catch (\Exception $e) {
            throw new \Exception("Não foi possivel salvar");
          }
        } else {
          $tarefa = $tarefaRepository->getRepository()::getEntity();

          $cliente = $clienteRepository->getRepository()->find($entity->cliente_uuid);
          if ($cliente) {
            $tarefa->hydrator([
              'cliente_uuid' => $entity->cliente_uuid,
              'user_uuid' => $cliente->usuario_uuid->value(),
              'post_uuid' => $entity->uuid,
              'tipo_uuid' => $entity->tipo_uuid,
              'data' => $entity->data,
              'tema' => $entity->tema,
              'informacao' => '<p>Sugestão de imagem:</p><p>' . $entity->sugestao . '</p><br><p>Texto:</p><p>' . $entity->texto . '</p><br><p>Legenda:</p><p>' . $entity->legenda . '</p>' . $cliente->assinatura ? ('<p>' . $cliente->assinatura . '</p>') : '',
              'prioridade' => 'baixa',
              'status' => 'fila',
              'setor' => 'criacao'
            ]);

            $tarefa->log->add(['nome' => $userInfo->nome, 'uuid' => $userInfo->uuid], 'Criou tarefa');
            $tarefa->log->add(['nome' => $userInfo->nome, 'uuid' => $userInfo->uuid], 'Definiu status: fila');

            try {
              $tarefaRepository->getRepository()->insert($tarefa->toArray());
            } catch (\Exception $e) {
              throw new \Exception("Não foi possivel salvar");
            }
          } else {
            throw new \Exception("Cliente não encontrado");
          }
        }
        return self::responseJson($response, $entity->toApi());
      } else {
        throw new \Exception("Não foi possível editar o registro.");
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
   * @throws \Exception
   */
  public function status(Request $request, Response $response): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'fotografia'])) {
      $uuid = $request->getParsedBody()['uuid'];
      /** @var PostEntity $entity */
      $entity = $this->postRepository->getRepository()->find($uuid);
      if (empty($entity)) {
        throw new HttpNotFoundException($request, "Registro inválido;");
      }

      $entity->status->set((int)!$entity->status->value());

      $registroEditado = $this->postRepository->getRepository()->update(['uuid' => $entity->uuid->value()], $entity->toSave('status'));
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
   * @param TarefaRepository $tarefaRepository
   * @param $uuid
   * @return Response
   * @throws Exception
   * @throws HttpBadRequestException
   * @throws HttpException
   * @throws HttpNotFoundException
   * @throws \Exception
   */
  public function delete(Request $request, Response $response, TarefaRepository $tarefaRepository, $uuid): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner'])) {
      /** @var PostEntity $entity */
      $entity = $this->postRepository->getRepository()->find($uuid);
      if (empty($entity)) {
        throw new HttpNotFoundException($request, "Registro inválido;");
      }
      $apagar = $this->postRepository->getRepository()->delete(['uuid' => $entity->uuid->value()]);

      if (!$apagar) {
        throw new HttpException($request, "Não foi possível apagar o registro.");
      }

      /** @var TarefaEntity $tarefa */
      $tarefa = $tarefaRepository->getRepository()->find($entity->uuid->value(), 'post_uuid');

      if ($tarefa) {
        $tarefaRepository->getRepository()->delete(['uuid' => $tarefa->uuid->value()]);
      }

      return self::responseJson($response, ['removido' => $entity->uuid->value()]);
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }
  }
}
