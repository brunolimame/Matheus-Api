<?php

namespace Modulo\Clientecontato\Controller;

use Boot\Provider\Jwt\JwtController;
use Core\Request\RequestApi;
use Core\Controller\BaseController;
use Doctrine\DBAL\Exception;
use HttpException;
use Modulo\Clientecontato\Entity\ClientecontatoEntity;
use Modulo\Clientecontato\Repository\ClientecontatoRepository;
use Core\Controller\Lib\ControllerView;
use Modulo\Clientecontato\Request\ClientecontatoRequestApi;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class ClientecontatoController extends BaseController
{
    /**@var ControllerView */
    public $view;
    /**@var RequestApi */
    public $api;
    /** @var ClientecontatoRepository */
    public $clientecontatoRepository;
    /** @var JwtController */
    public $jwt;

    public function __construct(
        ClientecontatoRepository $clientecontatoRepository,
        ClientecontatoRequestApi $clientecontatoRequestApi,
        JwtController            $jwtController
    )
    {
        $this->api = $clientecontatoRequestApi->getApi();
        $this->clientecontatoRepository = $clientecontatoRepository;
        $this->jwt = $jwtController;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws HttpBadRequestException
     */
    public function getContato(Request $request, Response $response): Response
    {
        if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'designer', 'atendimento'])) {
            $argumentos = $request->getParsedBody();
            $cliente_uuid = $argumentos['cliente_uuid'];

            $itens = [];

            if ($cliente_uuid) {
                $itens = $this->clientecontatoRepository->getRepository()->findBy(['cliente_uuid' => $cliente_uuid, 'status' => 1], [], 5000)->getItens();
                $tempArr = [];
                /** @var ClientecontatoEntity $item */
                foreach ($itens as $item) {
                    array_push($tempArr, [
                        'uuid' => $item->uuid->value(),
                        'nome' => $item->nome->value(),
                        'valor' => $item->valor->value()
                    ]);
                }
                $itens = $tempArr;
            }
        } else {
            throw new HttpBadRequestException($request, 'Acesso negado');
        }

        return self::responseJson($response, $itens);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws HttpBadRequestException
     */
    public function setContato(Request $request, Response $response): Response
    {
        if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'atendimento'])) {
            $argumentos = $request->getParsedBody();

            $entity = $this->clientecontatoRepository->getRepository()::getEntity();
            $entity->hydrator(['status' => true]);
            $entity->hydrator($request->getParsedBody());

            if (!$argumentos['nome'] || !$argumentos['valor']) {
                if (!$argumentos['nome']) {
                    throw new \Exception("Informe o nome");
                } elseif (!$argumentos['valor']) {
                    throw new \Exception("Informe o valor");
                } else {
                    throw new \Exception("Erro desconhecido");
                }
            }

            try {
                $novoRegistroId = $this->clientecontatoRepository->getRepository()->insert($entity->toSave('novo'));
            } catch (\Exception $e) {
                throw new \Exception("Não foi possível salvar");
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
     * @return Response
     * @throws Exception
     * @throws HttpBadRequestException
     * @throws HttpNotFoundException
     * @throws HttpException
     */
    public function delete(Request $request, Response $response): Response
    {
        if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner', 'atendimento'])) {
            $uuid = $request->getParsedBody()['uuid'];

            /** @var ClientecontatoEntity $entity */
            $entity = $this->clientecontatoRepository->getRepository()->find($uuid);
            if (empty($entity)) {
                throw new HttpNotFoundException($request, "Registro inválido;");
            }
            $entity->status->set(false);
            $apagar = $this->clientecontatoRepository->getRepository()->update(['uuid' => $entity->uuid->value()], $entity->toSave('excluiu'));

            if (!$apagar) {
                throw new HttpException($request, "Não foi possível apagar o registro.");
            }

            return self::responseJson($response, true);
        } else {
            throw new HttpBadRequestException($request, 'Acesso negado');
        }
    }
}
