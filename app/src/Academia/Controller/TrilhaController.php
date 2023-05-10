<?php

namespace Modulo\Academia\Controller;

use Boot\Provider\Jwt\JwtController;
use Core\Controller\BaseController;
use Doctrine\DBAL\Exception;
use DOMDocument;
use DOMNode;
use Modulo\Cliente\Entity\ClienteEntity;
use Modulo\Cliente\Repository\ClienteRepository;
use Modulo\Post\Entity\PostEntity;
use Modulo\Post\Repository\PostRepository;
use Modulo\Academia\Entity\TrilhaEntity;
use Modulo\Academia\Repository\TrilhaRepository;
use Modulo\User\Entity\UserEntity;
use Modulo\User\Repository\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;

class TrilhaController extends BaseController
{
  /** @var TrilhaRepository */
  public $trilhaRepository;
  /** @var JwtController */
  public $jwt;

  public function __construct(TrilhaRepository $trilhaRepository, JwtController $jwtController)
  {
    $this->trilhaRepository = $trilhaRepository;
    $this->jwt = $jwtController;
  }

  /**
   * @param $inicio
   * @param $source
   * @return false|string|void
   */
  private function after($inicio, $source)
  {
    if (!is_bool(strpos($source, $inicio)))
      return substr($source, strpos($source, $inicio) + strlen($inicio));
  }

  /**
   * @param $inicio
   * @param $source
   * @return false|string
   */
  private function before($inicio, $source)
  {
    return substr($source, 0, strpos($source, $inicio));
  }

  /**
   * @param $inicio
   * @param $fim
   * @param $source
   * @return false|string
   */
  private function between($inicio, $fim, $source)
  {
    return $this->before($fim, $this->after($inicio, $source));
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
   * @return Response
   * @throws Exception
   * @throws HttpBadRequestException
   * @throws \Exception
   */
  public function findAll(Request $request, Response $response, ClienteRepository $repoCliente, UserRepository $userRepository): Response
  {
    $opts = array('http' =>
      array(
        'method' => 'GET'
      )
    );

    $context = stream_context_create($opts);
    $html = file_get_contents('https://agriculture1.newholland.com/lar/pt-br', false, $context);
    $result = str_replace(['	', '
', '  '], '', $html);
    $parcial = '';
    if (str_contains($result, '<ul class="level-3">' && str_contains($result, '<div class="sliderY mobile-hidden">'))) {
      $parcial = $this->between('<ul class="level-3">', '</ul><div class="sliderY mobile-hidden">', $result);
    }

    return self::responseJson($response, $parcial, 200);

//    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'designer', 'atendimento', 'qa', 'desenvolvimento', 'fotografia'])) {
//      $argumentos = $request->getParsedBody();
//
//      switch ($nivel) {
//        case 'admin':
//        case 'sadmin':
//          //TODO Mostrar Todos
//          break;
//        case 'atendimento':
//          //TODO Mostrar trilhas de atendimento
//          break;
//        case 'qa':
//        //TODO Mostrar trilhas de Quality Assurance
//          break;
//        case 'planner':
//          //TODO Mostrar trilhas de Planner
//          break;
//        case 'colaborador':
//          //TODO Mostrar trilhas de Colaborador
//          break;
//        default:
//          $trilhas = [];
//      }
//
//      $itens = array_reduce($trilhas, function ($result, $trilha) use (&$repoCliente, &$userRepository, &$tipoAcademiaRepository) {
//        /** @var AcademiaEntity $trilha */
//        /** @var ClienteEntity $cliente */
//        $cliente = $repoCliente->getRepository()->find($trilha->cliente_uuid);
//        /** @var UserEntity $colaborador */
//        $colaborador = $userRepository->getRepository()->find($trilha->user_uuid);
//        if ($cliente && $colaborador) {
//          $result[] = [
//            'uuid' => $trilha->uuid->value(),
//            'cliente' => $cliente->razao_social->value(),
//            'tema' => $trilha->tema->value(),
//            'colaborador' => ['uuid' => $colaborador->uuid->value(), 'nome' => $colaborador->nome->value(), 'foto' => $colaborador->foto->value()],
//            'data' => $trilha->data->value(),
//            'prioridade' => $trilha->prioridade->value(),
//            'status' => $trilha->status->value(),
//            'isAlteracao' => (str_contains($trilha->log->value(), 'Alterou status: revisao => execucao') || str_contains($trilha->log->value(), 'Alterou status: cliente => fila'))
//          ];
//        }
//        return $result;
//      }, []);
//
//    } else {
//      throw new HttpBadRequestException($request, 'Acesso negado');
//    }
//
//    return self::responseJson($response, $itens, 200);
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

      /** @var TrilhaEntity $entity */
      $entity = $this->trilhaRepository->getRepository()::getEntity();
      $entity->hydrator($argumentos);
      $entity->hydrator(['status' => 1]);
      $entity->log->add(['nome' => $userInfo->nome, 'uuid' => $userInfo->uuid], 'Criou trilha');

      try {
        $novoRegistroId = $this->trilhaRepository->getRepository()->insert($entity->toArray());
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
      $userInfo = $this->jwt->decodeNovo($request);

      /** @var TrilhaEntity $entity */
      $entity = $this->trilhaRepository->getRepository()->findBy(['uuid' => $uuid])->getItem();

      if (!$entity) {
        throw new \Exception("Não foi possivel encontrar o registro");
      }

      $entity->hydrator($argumentos);
      $entity->log->add(['nome' => $userInfo->nome, 'uuid' => $userInfo->uuid], 'Alterou trilha');

      try {
        $novoRegistroId = $this->trilhaRepository->getRepository()->update($entity->toArray());
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
   * @param ClienteRepository $repoCliente
   * @param UserRepository $userRepository
   * @param TipoAcademiaRepository $tipoAcademiaRepository
   * @param PostRepository $postRepository
   * @param $uuid
   * @return Response
   * @throws Exception
   * @throws HttpBadRequestException
   */
  public function find(Request $request, Response $response, ClienteRepository $repoCliente, UserRepository $userRepository, TipoAcademiaRepository $tipoAcademiaRepository, PostRepository $postRepository, $uuid): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'designer', 'desenvolvimento', 'fotografia'])) {
      /** @var AcademiaEntity $trilha */
      $trilha = $this->trilhaRepository->getRepository()->find($uuid);
      $item = [];

      if ($trilha) {
        $item = [
          'uuid' => $trilha->uuid->value(),
          'tema' => $trilha->tema->value(),
          'setor' => $trilha->setor->value(),
          'data' => $trilha->data->value(),
          'informacao' => $trilha->informacao->value(),
          'prioridade' => ['uuid' => $trilha->prioridade->value(), 'nome' => ($trilha->prioridade->value() == 'alta' || $trilha->prioridade->value() == 'baixa') ? ucfirst($trilha->prioridade->value()) : "Média"],
          'status' => $trilha->status->value(),
        ];

        /** @var ClienteEntity $cliente */
        $cliente = $repoCliente->getRepository()->find($trilha->cliente_uuid);

        if ($cliente) {
          $item['cliente'] = [
            'uuid' => $cliente->uuid->value(),
            'razao_social' => $cliente->razao_social->value()
          ];
          $item['assinatura'] = $cliente->assinatura->value() ?: '';
        }

        /** @var UserEntity $designer */
        $designer = $userRepository->getRepository()->find($trilha->user_uuid);

        if ($designer) {
          $item['designer'] = [
            'uuid' => $designer->uuid->value(),
            'nome' => $designer->nome->value(),
            'foto' => $designer->foto->value(),
            'nivel' => $designer->nivel->value()
          ];
        }

        /** @var TipoAcademiaEntity $tipo */
        $tipo = $tipoAcademiaRepository->getRepository()->find($trilha->tipo_uuid);

        if ($tipo) {
          $item['tipo'] = [
            'uuid' => $tipo->uuid->value(),
            'nome' => $tipo->nome->value(),
            'pontos' => $tipo->pontos->value()
          ];

          if ($trilha->post_uuid->value()) {
            /** @var PostEntity $post */
            $post = $postRepository->getRepository()->find($trilha->post_uuid->value());
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
