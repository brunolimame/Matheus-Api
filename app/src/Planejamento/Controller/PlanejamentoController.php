<?php

namespace Modulo\Planejamento\Controller;

use Boot\Provider\Jwt\JwtController;
use Core\Controller\BaseController;
use Exception;
use Modulo\Cliente\Entity\ClienteEntity;
use Modulo\Cliente\Repository\ClienteRepository;
use Modulo\Post\Entity\PostEntity;
use Modulo\Post\Repository\PostRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;

class PlanejamentoController extends BaseController
{
  /** @var PostRepository */
  public PostRepository $postRepository;
  /** @var JwtController */
  public JwtController $jwt;

  public function __construct(PostRepository $postRepository, JwtController $jwtController)
  {
    $this->postRepository = $postRepository;
    $this->jwt = $jwtController;
  }

  /**
   * @param Request $request
   * @param Response $response
   * @param ClienteRepository $repoCliente
   * @param $cliente
   * @param $mes
   * @param $ano
   * @return Response
   * @throws HttpBadRequestException
   * @throws Exception
   */
  public function find(Request $request, Response $response, ClienteRepository $repoCliente, $cliente, $mes, $ano): Response
  {
    $meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
    $semana = ['DOMINGO', 'SEGUNDA', 'TERÇA', 'QUARTA', 'QUINTA', 'SEXTA', 'SÁBADO'];
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'designer', 'fotografia'])) {
      /** @var string $dataBusca */
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
      /** @var ClienteEntity $cliente */
      $clienteEntity = $repoCliente->getRepository()->findBy(['uuid' => $cliente])->getItem();
      $posts = $this->postRepository->getRepository()->findBy(['cliente_uuid' => $cliente], ['data' => 'ASC'], 5000, 1, ['busca' => $dataBusca, 'campos_busca' => ['data']])->getItens();

      $info = [
        'razao_social' => $clienteEntity->razao_social->value(),
        'posts_total' => $clienteEntity->posts->value(),
        'posts_usado' => count($posts),
        'assinatura' => $clienteEntity->assinatura->value(),
        'informacao' => $clienteEntity->informacao->value()
      ];

      $itens = [];
      /** @var PostEntity $post */
      foreach ($posts as $post) {
        $data = $post->data->value();
        $data = explode('-', $data);
        $novaData = $data[2] . '/' . $data[1] . '/' . $data[0];
        array_push($itens, [
          'uuid' => $post->uuid->value(),
          'data' => $novaData,
          'dia_semana' => $semana[date_create_from_format('Y-m-d', $post->data->value())->format('w')],
          'tema' => $post->tema->value(),
          'sugestao' => $post->sugestao->__toString(),
          'texto' => $post->texto->__toString(),
          'legenda' => $post->legenda->__toString(),
          'feito' => $post->feito->value()
        ]);
      }
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }

    return self::responseJson($response, (object)['info' => $info, 'itens' => $itens]);
  }

  /**
   * @param Request $request
   * @param Response $response
   * @param $post_uuid
   * @return Response
   * @throws HttpBadRequestException
   * @throws Exception
   */
  public function toggleFeito(Request $request, Response $response, $post_uuid): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'designer', 'fotografia'])) {
      /** @var PostEntity $post */
      $post = $this->postRepository->getRepository()->findBy(['uuid' => $post_uuid])->getItem();

      if (!empty($post)) {
        $post->feito->set((int)!$post->feito->value());
        $registroEditado = $this->postRepository->getRepository()->update(['uuid' => $post->uuid->value()], $post->toSave('feito'));
        if ($registroEditado) {
          return self::responseJson($response, $post->toApi());
        } else {
          throw new Exception("Não foi possível alteara o status.");
        }
      } else {
        throw new HttpBadRequestException($request, 'Registro não encontrado');
      }
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }
  }
}
