<?php

namespace Modulo\Registro\Controller;

use Boot\Provider\Jwt\JwtController;
use Core\Lib\Acl\AclCheck;
use Core\Request\RequestApi;
use Core\Controller\BaseController;
use Modulo\Registro\Entity\RegistroEntity;
use Modulo\Registro\Repository\Repository as RegistroRepository;
use Modulo\Registro\Request\RegistroRequestApi;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Controller extends BaseController
{
	public RequestApi $api;
	public RegistroRepository $registroRepository;
	public AclCheck $acl;
	public JwtController $jwt;
	
	public function __construct(
		RegistroRepository $registroRepository,
		RegistroRequestApi $registroRequestApi,
		JwtController      $jwtController
	)
	{
		$this->api = $registroRequestApi->getApi();
		$this->registroRepository = $registroRepository;
		$this->jwt = $jwtController;
	}
	
	/**
	 * @param Request $request
	 * @param Response $response
	 * @return Response
	 * @throws \Exception
	 */
	public function findAll(Request $request, Response $response): Response
	{
		$params = $request->getQueryParams();
		$active = $params['active'];
		$time = $params['time'];
		$mac = $params['mac'];
		$ip = $request->getServerParams()['REMOTE_ADDR'];
		$date = date_format(new \DateTime(), 'd/m/Y');
		
		if (!$time || !$mac || !$ip || !$date) {
			return self::jsonResponse($response, false);
		}
		
		/** @var RegistroEntity $registro */
		$registro = $this->registroRepository->getRepository()->findBy(['ip' => $ip, 'mac' => $mac, 'data' => $date], [], 1)->getItem();
		if ($registro) {
			if ($registro->ativo->value() != ($active == 'true') || $registro->tempo->value() != $time) {
				$hmsAtual = explode(":", $registro->tempo->value());
				$hmsNova = explode(":", $time);
				$segundosNovo = $hmsAtual[2] + $hmsNova[2];
				$minutosNovo = $hmsAtual[1] + $hmsNova[1];
				$horasNovo = $hmsAtual[0] + $hmsNova[0];
				
				while ($segundosNovo > 59) {
					$minutosNovo++;
					$segundosNovo -= 60;
				}
				
				while ($minutosNovo > 59) {
					$horasNovo++;
					$minutosNovo -= 60;
				}
				
				$registro->hydrator([
					'ativo' => ($active == 'true' ? 1 : 0),
					'tempo' => ($horasNovo < 10 ? '0' . $horasNovo : $horasNovo) . ':' . ($minutosNovo < 10 ? '0' . $minutosNovo : $minutosNovo) . ':' . ($segundosNovo  < 10 ? '0' . $segundosNovo : $segundosNovo),
				]);
				$sucesso = $this->registroRepository->getRepository()->update(['uuid' => $registro->uuid->value()], $registro->toSave());
			} else {
				$sucesso = true;
			}
		} else {
			$registro = $this->registroRepository->getRepository()::getEntity();
			$registro->hydrator([
				'ativo' => ($active == 'true' ? 1 : 0),
				'tempo' => $time,
				'mac' => $mac,
				'data' => $date,
				'ip' => $ip
			]);
			$sucesso = $this->registroRepository->getRepository()->insert($registro->toSave());
		}
		
		return self::jsonResponse($response, $sucesso);
	}

//  /**
//   * @param Request $request
//   * @param Response $response
//   * @param $uuid
//   * @return Response
//   * @throws HttpBadRequestException
//   * @throws HttpNotFoundException
//   * @throws \Exception
//   */
//  public function findOne(Request $request, Response $response, $uuid): Response
//  {
//    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
//      /** @var RegistroEntity $item */
//      $item = $this->repository->getRepository()->findBy(['uuid' => $uuid], [], 1)->itensToArray();
//
//      if (empty($item)) {
//        throw new HttpNotFoundException($request, "Registro inválido");
//      } else {
//        return self::jsonResponse($response, $item[0]);
//      }
//    } else {
//      throw new HttpBadRequestException($request, 'Acesso negado');
//    }
//  }
//
//  /**
//   * @param Request $request
//   * @param Response $response
//   * @return Response
//   * @throws Exception
//   * @throws HttpBadRequestException
//   */
//  public function create(Request $request, Response $response): Response
//  {
//    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
//      $argumentos = $request->getParsedBody();
//      unset($argumentos['uuid']);
//
//      /** @var RegistroEntity $entity */
//      $entity = $this->repository->getRepository()::getEntity();
//      $entity->hydrator([
//        'status' => true
//      ]);
//
//      $entity->hydrator($argumentos);
//
//      try {
//        $novoRegistroId = $this->repository->getRepository()->insert($entity->toSave('novo'));
//      } catch (\Exception $e) {
//        throw new Exception("Não foi possivel salvar");
//      }
//
//      if ($novoRegistroId) {
//        return self::jsonResponse($response, [], 201);
//      } else {
//        throw new Exception("Não foi possível criar o registro.");
//      }
//    } else {
//      throw new HttpBadRequestException($request, 'Acesso negado');
//    }
//  }
//
//  /**
//   * @param Request $request
//   * @param Response $response
//   * @param $uuid
//   * @return Response
//   * @throws Exception
//   * @throws HttpBadRequestException
//   * @throws HttpNotFoundException
//   * @throws \Exception
//   */
//  public function update(Request $request, Response $response, $uuid): Response
//  {
//    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
//      $argumentos = $request->getParsedBody();
//
//      /** @var RegistroEntity $entity */
//      $entity = $this->repository->getRepository()->find($uuid);
//      if (empty($entity)) {
//        throw new HttpNotFoundException($request, "Registro inválido");
//      }
//
//      $entity->hydrator($argumentos);
//
//      $registroEditado = $this->repository->getRepository()->update(['uuid' => $entity->uuid->value()], $entity->toSave('editar'));
//      if ($registroEditado) {
//        return self::jsonResponse($response);
//      } else {
//        throw new \Exception("Não foi possível editar o registro.");
//      }
//    } else {
//      throw new HttpBadRequestException($request, 'Acesso negado');
//    }
//  }
//
//  /**
//   * @param Request $request
//   * @param Response $response
//   * @return Response
//   * @throws Exception
//   * @throws HttpBadRequestException
//   * @throws HttpNotFoundException
//   * @throws \Exception
//   */
//  public function delete(Request $request, Response $response): Response
//  {
//    if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
//      $uuid = $request->getParsedBody()['uuid'];
//
//      $entity = $this->repository->getRepository()->find($uuid);
//      if (empty($entity)) {
//        throw new HttpNotFoundException($request, "Registro inválido;");
//      }
//      $apagar = $this->repository->getRepository()->delete(['uuid' => $entity->uuid->value()]);
//
//      if (!$apagar) {
//        throw new HttpException($request, "Não foi possível apagar o registro.");
//      }
//
//      return self::jsonResponse($response);
//    } else {
//      throw new HttpBadRequestException($request, 'Acesso negado');
//    }
//  }
}
