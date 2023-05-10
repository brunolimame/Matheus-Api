<?php

namespace Modulo\Comunicado\Controller;

use Boot\Provider\Jwt\JwtController;
use Core\Request\RequestApi;
use Core\Controller\BaseController;
use Doctrine\DBAL\Exception;
use Modulo\Comunicado\Entity\ComunicadoEntity;
use Modulo\Comunicado\Repository\ComunicadoRepository;
use Core\Controller\Lib\ControllerView;
use Modulo\Comunicado\Request\ComunicadoRequestApi;
use Modulo\User\Repository\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class ComunicadoController extends BaseController
{
    /**@var ControllerView */
    public $view;
    /**@var RequestApi */
    public $api;
    /** @var ComunicadoRepository */
    public $comunicadoRepository;
    /** @var JwtController */
    public $jwt;

    public function __construct(
        ComunicadoRepository $comunicadoRepository,
        ComunicadoRequestApi $comunicadoRequestApi,
        JwtController        $jwtController
    )
    {
        $this->api = $comunicadoRequestApi->getApi();
        $this->comunicadoRepository = $comunicadoRepository;
        $this->jwt = $jwtController;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param UserRepository $repoUser
     * @return Response
     * @throws HttpBadRequestException
     */
    public function getAllComunicados(Request $request, Response $response, UserRepository $repoUser): Response
    {
        if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'atendimento'])) {
            return self::responseJson($response, $this->comunicadoRepository->getRepository()->findBy(['status' => 1], ['criado' => 'DESC'], 5000)->itensToArray());
        } else {
            throw new HttpBadRequestException($request, 'Acesso negado');
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param UserRepository $repoUser
     * @return Response
     * @throws HttpBadRequestException
     */
    public function getComunicado(Request $request, Response $response, UserRepository $repoUser): Response
    {
        if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'atendimento'])) {
            $argumentos = $request->getParsedBody();

            return self::responseJson($response, $this->comunicadoRepository->getRepository()->find($argumentos['comunicado_uuid'])->toApi());
        } else {
            throw new HttpBadRequestException($request, 'Acesso negado');
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws \Exception
     */
    public function getComunicados(Request $request, Response $response): Response
    {
        $info_decode = $this->jwt->decodeNovo($request);

        if (in_array("admin", $info_decode->nivel) || in_array("sadmin", $info_decode->nivel)) {
            $itens = $this->comunicadoRepository->getRepository()->findBy(['status' => 1], ['criado' => 'DESC'], 5000)->itensToArray();
        } else {
            $itens = $this->comunicadoRepository->findByUuidOrSetor($info_decode->uuid, (in_array("designer", $info_decode->nivel) || in_array("planner", $info_decode->nivel)), in_array("desenvolvimento", $info_decode->nivel), in_array("fotografia", $info_decode->nivel), in_array("atendimento", $info_decode->nivel))->itensToArray();
        }
        return self::responseJson($response, $itens);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws HttpBadRequestException
     * @throws HttpNotFoundException
     * @throws Exception
     */
    public function salvar(Request $request, Response $response): Response
    {
        if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'atendimento'])) {
            $argumentos = $request->getParsedBody();
            $uuid = $argumentos['uuid'];

            if ($uuid == '') {
                unset($argumentos['uuid']);
            }

            if (empty($uuid)) {
                $entity = $this->comunicadoRepository->getRepository()::getEntity();
                $entity->hydrator([
                    'status' => true,
                    'fixo' => false,
                    'data' => (new \DateTime())->format('Y-m-d')
                ]);
            } else {
                $entity = $this->comunicadoRepository->getRepository()->find($uuid);
                if (empty($entity)) {
                    throw new HttpNotFoundException($request, "Registro inválido");
                }
            }

            if ($argumentos['setor'] == '') {
                unset($argumentos['setor']);
            }
            if ($argumentos['user_uuid'] == '') {
                unset($argumentos['user_uuid']);
            }
            $entity->hydrator($argumentos);


            if ($entity->id->value() > 0) {
                $registroEditado = $this->comunicadoRepository->getRepository()->update(['uuid' => $entity->uuid->value()], $entity->toSave('editar'));
                if ($registroEditado) {
                    return self::responseJson($response, $entity->toApi());
                } else {
                    throw new \Exception("Não foi possível editar o registro.");
                }
            } else {
                try {
                    $novoRegistroId = $this->comunicadoRepository->getRepository()->insert($entity->toSave('novo'));
                } catch (\Exception $e) {
                    throw new \Exception("Não foi possivel salvar");
                }

                if ($novoRegistroId) {
                    $novosDados = $this->comunicadoRepository->getRepository()->find($novoRegistroId, 'id');
                    return self::responseJson($response, $novosDados->toApi());
                } else {
                    throw new \Exception("Não foi possível criar o registro.");
                }
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
     */
    public function status(Request $request, Response $response): Response
    {
        if ($this->jwt->validaAcesso($request, ['sadmin', 'admin', 'planner'])) {
            $uuid = $request->getParsedBody()['uuid'];
            /** @var ComunicadoEntity $entity */
            $entity = $this->comunicadoRepository->getRepository()->find($uuid);
            if (empty($entity)) {
                throw new HttpNotFoundException($request, "Registro inválido;");
            }

            $entity->status->set((int)(!$entity->status->value()));

            $registroEditado = $this->comunicadoRepository->getRepository()->update(['uuid' => $entity->uuid->value()], $entity->toSave('status'));
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
     * @return Response
     * @throws Exception
     * @throws HttpBadRequestException
     * @throws HttpNotFoundException
     */
    public function fixo(Request $request, Response $response): Response
    {
        if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
            $uuid = $request->getParsedBody()['uuid'];

            /** @var ComunicadoEntity $entity */
            $entity = $this->comunicadoRepository->getRepository()->find($uuid);
            if (empty($entity)) {
                throw new HttpNotFoundException($request, "Registro inválido;");
            }

            $entity->fixo->set((int)(!$entity->fixo->value()));

            $registroEditado = $this->comunicadoRepository->getRepository()->update(['uuid' => $entity->uuid->value()], $entity->toSave('fixo'));
            if ($registroEditado) {
                return self::responseJson($response, $entity->toApi());
            } else {
                throw new \Exception("Não foi possível alteara o status.");
            }
        } else {
            throw new HttpBadRequestException($request, 'Acesso negado');
        }
    }


}
