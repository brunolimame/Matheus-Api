<?php

namespace Modulo\Video\Controller;

use Core\Lib\Arquivo\Imagem;
use Core\Repository\FactoryFindBy;
use Core\Controller\BaseController;
use Modulo\Video\Entity\VideoEntity;
use Slim\Exception\HttpNotFoundException;
use Modulo\Video\Provider\VideoAclProvider;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Exception\HttpBadRequestException;
use Modulo\Video\Repository\VideoRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class VideoApiController extends BaseController
{

    /** @var VideoRepository */
    public $videoRepository;
    /** @var AclCheck */
    public $acl;

    public function __construct(VideoRepository $videoRepository, VideoAclProvider $videoAclProvider)
    {
        $this->videoRepository = $videoRepository;
        $this->acl = $videoAclProvider->getAcl();
    }

    public function index(Request $request, Response $response)
    {

        $uuid = $request->getQueryParams()['uuid'];
        $definirStatus = $this->acl->isAllowed('ler-todos') ? null : 1;
        if (!is_null($uuid)) {
            if ($this->acl->isAllowed('ler-todos')) {
                $itens = $this->videoRepository->getRepository()->find($uuid);
            } else {
                $itens = FactoryFindBy::factory($this->videoRepository->getRepository(), ['where' => "uuid#{$uuid}"], $definirStatus)->getItem();
            }

            if (empty($itens)) {
                throw new HttpNotFoundException($request, "Registro inválido");
            }
        } else {
            $itens = FactoryFindBy::factory($this->videoRepository->getRepository(), $request->getQueryParams(), $definirStatus);
        }

        return self::responseJson($response, $itens->toApi());
    }

    public function live(Request $request, Response $response)
    {
        $itens = $this->videoRepository->getRepository()->findBy(['live' => 1, 'status' => 1], ['criado' => 'ASC']);
        return self::responseJson($response, $itens->toApi());
    }

    public function local(Request $request, Response $response)
    {
        return self::responseJson($response, VideoEntity::LOCAIS_SUPORTADOS);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws HttpNotFoundException
     * @throws \Doctrine\DBAL\Exception
     */
    public function salvar(Request $request, Response $response)
    {
        $uuid = $request->getQueryParams()['uuid'];
        if (empty($uuid)) {
            if (!$this->acl->isAllowed('novo')) {
                throw new HttpBadRequestException($request, 'Acesso negado');
            }
            /** @var VideoEntity $entity */
            $entity = $this->videoRepository->getRepository()::getEntity();
            $entity->status->set(true);
        } else {
            if (!$this->acl->isAllowed('editar')) {
                throw new HttpBadRequestException($request, 'Acesso negado');
            }
            $entity = $this->videoRepository->getRepository()->find($uuid);
            if (empty($entity)) {
                throw new HttpNotFoundException($request, "Registro inválido;");
            }
        }
        $entity->hydrator($request->getParsedBody());
        $parametrosArquivo = $entity::getParametrosParaArquivo();
        $requerArquivos    = $request->getUploadedFiles();
        if (!empty($requerArquivos['foto'])) {
            /** @var UploadedFileInterface $foto */
            $foto = $requerArquivos['foto'];
            if ($foto->getSize() > 0) {
                $novasImagens = Imagem::redimencionar($foto, $parametrosArquivo);
                $entity->foto->set($novasImagens->dados->nomeNovo);
                $entity->foto_link->set($novasImagens->arquivos->value());
            }
        }
        if (empty($entity->foto->value())) {
            $entity->foto_link->set(null);
        }

        if ($entity->id->value() > 0) {
            $registroEditado = $this->videoRepository->getRepository()->update(['uuid' => $entity->uuid->value()], $entity->toSave('editar'));
            if ($registroEditado) {
                return self::responseJson($response, $entity->toApi());
            } else {
                throw new \Exception("Não foi possível editar o registro.");
            }
        } else {
            $novoRegistroId = $this->videoRepository->getRepository()->insert($entity->toSave('novo'));
            if ($novoRegistroId) {
                $novosDados = $this->videoRepository->getRepository()->find($novoRegistroId, 'id');
                return self::responseJson($response, $novosDados->toApi());
            } else {
                throw new \Exception("Não foi possível criar o registro.");
            }
        }
    }

    /**
     * @param $status
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws HttpNotFoundException
     * @throws \Doctrine\DBAL\Exception
     */
    public function status($status, Request $request, Response $response)
    {
        if (!$this->acl->isAllowed('status')) {
            throw new HttpBadRequestException($request, 'Acesso negado');
        }
        $uuid = $request->getQueryParams()['uuid'];
        $entity = $this->videoRepository->getRepository()->find($uuid);
        if (empty($entity)) {
            throw new HttpNotFoundException($request, "Registro inválido;");
        }
        $novoStatus = (int)($status == 'a');

        $entity->status->set($novoStatus);

        $registroEditado = $this->videoRepository->getRepository()->update(['uuid' => $entity->uuid->value()], $entity->toSave('status'));
        if ($registroEditado) {
            return self::responseJson($response, $entity->toApi());
        } else {
            throw new \Exception("Não foi possível alteara o status.");
        }
    }

    /**
     * @param $id
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws HttpNotFoundException
     * @throws \Doctrine\DBAL\Exception
     * @throws \HttpException
     */
    public function delete(Request $request, Response $response)
    {
        if (!$this->acl->isAllowed('delete')) {
            throw new HttpBadRequestException($request, 'Acesso negado');
        }
        $uuid = $request->getQueryParams()['uuid'];
        $entity = $this->videoRepository->getRepository()->find($uuid);
        if (empty($entity)) {
            throw new HttpNotFoundException($request, "Registro inválido;");
        }
        $apagar = $this->videoRepository->getRepository()->delete(['uuid' => $entity->uuid->value()]);

        if (!$apagar) {
            throw new \HttpException($request, "Não foi possível apagar o registro.");
        }

        return self::responseJson($response, ['removido' => $entity->uuid->value()]);
    }
}
