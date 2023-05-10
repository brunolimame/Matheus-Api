<?php

namespace Modulo\Versao\Controller;

use Boot\Provider\Jwt\JwtController;
use Core\Lib\Acl\AclCheck;
use Core\Request\RequestApi;
use Core\Controller\BaseController;
use Doctrine\DBAL\Exception;
use HttpException;
use Modulo\Cliente\Repository\ClienteRepository;
use Modulo\Versao\Entity\VersaoEntity;
use Modulo\Versao\Repository\VersaoRepository;
use Core\Controller\Lib\ControllerView;
use Modulo\Versao\Request\VersaoRequestApi;
use Modulo\Tarefa\Entity\TarefaEntity;
use Modulo\Tarefa\Repository\TarefaRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class VersaoController extends BaseController
{
  /**@var ControllerView */
  public $view;
  /**@var RequestApi */
  public $api;
  /** @var VersaoRepository */
  public $versaoRepository;
  /** @var AclCheck */
  public $acl;
  /** @var JwtController */
  public $jwt;

  public function __construct(
    VersaoRepository $versaoRepository,
    VersaoRequestApi $versaoRequestApi,
    JwtController    $jwtController
  )
  {
    $this->api = $versaoRequestApi->getApi();
    $this->versaoRepository = $versaoRepository;
    $this->jwt = $jwtController;
  }

  /**
   * @param Request $request
   * @param Response $response
   * @return Response
   * @throws Exception
   */
  public function find(Request $request, Response $response): Response
  {
    /** @var VersaoEntity $versao */
    $versao = $this->versaoRepository->getRepository()->find(1, 'id');

    return self::responseJsonNovo($response, $versao->versao->value());
  }

  /**
   * @param Request $request
   * @param Response $response
   * @return Response
   * @throws Exception
   * @throws HttpBadRequestException
   * @throws HttpNotFoundException
   */
  public function update(Request $request, Response $response): Response
  {
    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
      $argumentos = $request->getParsedBody();

      $uuid = $argumentos['uuid'];

      $entity = $this->versaoRepository->getRepository()->find($uuid);
      if (empty($entity)) {
        throw new HttpNotFoundException($request, "Registro inválido;");
      }

      $entity->hydrator($argumentos);

      $registroSalvo = $this->versaoRepository->getRepository()->update(['uuid' => $uuid], $entity->toSave('editar'));

      if ($registroSalvo) {
        return self::responseJson($response, $entity->toApi());
      } else {
        throw new \Exception("Não foi possivel salvar");
      }
    } else {
      throw new HttpBadRequestException($request, 'Acesso negado');
    }
  }
}
