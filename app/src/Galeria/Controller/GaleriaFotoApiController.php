<?php

namespace Modulo\Galeria\Controller;

use Core\Lib\Arquivo\Imagem;
use Core\Repository\FactoryFindBy;
use Core\Controller\BaseController;
use Psr\Container\ContainerInterface;
use Modulo\Galeria\Entity\GaleriaEntity;
use Slim\Exception\HttpNotFoundException;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Exception\HttpBadRequestException;
use Modulo\Galeria\Entity\GaleriaFotoEntity;
use Modulo\Galeria\Provider\GaleriaAclProvider;
use Modulo\Galeria\Repository\GaleriaRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Modulo\Galeria\Repository\GaleriaFotoRepository;
use Psr\Http\Message\ServerRequestInterface as Request;

class GaleriaFotoApiController extends BaseController
{

    /** @var GaleriaRepository */
    public $galeriaRepository;
    /** @var GaleriaFotoRepository */
    public $galeriaFotoRepository;
    /** @var AclCheck */
    public $acl;

    public function __construct(GaleriaRepository $galeriaRepository, GaleriaFotoRepository $galeriaFotoRepository, GaleriaAclProvider $galeriaAclProvider)
    {
        $this->galeriaRepository = $galeriaRepository;
        $this->galeriaFotoRepository = $galeriaFotoRepository;
        $this->acl = $galeriaAclProvider->getAcl();
    }

    /**
     * @param $galeria
     * @param null $id
     * @param Request $request
     * @param Response $response
     * @param ContainerInterface $container
     * @return Response
     * @throws HttpNotFoundException
     * @throws \Doctrine\DBAL\Exception
     */
    public function index(Request $request, Response $response)
    {

        $parametrosQuery          = $request->getQueryParams();
        $galeriaEntity = $this->verificarRegistroGaleria($parametrosQuery['galeria'], $request);
        $uuid = $parametrosQuery['uuid'];
        unset($parametrosQuery['galeria']);

        if (!is_null($uuid)) {
            $parametrosQuery['where'] = "uuid#{$uuid}";
            if (!$this->acl->isAllowed('ler-todos')) {
                $parametrosQuery['where'] .= ",status#1";
            }
            $itens = FactoryFindBy::factory($this->galeriaFotoRepository->getRepository(), $parametrosQuery)->getItem();

            if (empty($itens)) {
                throw new HttpNotFoundException($request, "Registro inválido");
            }
        } else {
            $whereGaleria             = "galeria_uuid#{$galeriaEntity->uuid->value()}";
            $parametrosQuery['where'] = empty($parametrosQuery['where']) ? $whereGaleria : $parametrosQuery['where'] . ",{$whereGaleria}";
            if (!$this->acl->isAllowed('ler-todos')) {
                $parametrosQuery['where'] .= ',status#1';
            }
            $itens = FactoryFindBy::factory($this->galeriaFotoRepository->getRepository(), $parametrosQuery);
        }

        return self::responseJson($response, $itens->toApi());
    }

    /**
     * @param $galeria
     * @param null $id
     * @param Request $request
     * @param Response $response
     * @param ContainerInterface $container
     * @return Response
     * @throws HttpNotFoundException
     * @throws \Doctrine\DBAL\Exception
     */
    public function salvar(Request $request, Response $response)
    {
        $parametrosQuery          = $request->getQueryParams();
        $galeriaEntity = $this->verificarRegistroGaleria($parametrosQuery['galeria'], $request);
        $uuid = $parametrosQuery['uuid'];
        unset($parametrosQuery['galeria']);

        if (empty($uuid)) {
            if (!$this->acl->isAllowed('novo')) {
                throw new HttpBadRequestException($request, 'Acesso negado');
            }
            /** @var GaleriaFotoEntity $entity */
            $entity = $this->galeriaFotoRepository->getRepository()::getEntity();
            $entity->galeria_id->set($galeriaEntity->uuid->value());
            $entity->status->set(true);
            $entity->ordem->set($this->galeriaFotoRepository->getRepository()->findByLastOrder()->proxima);
            $entity->carregarDadosExtras();
        } else {
            if (!$this->acl->isAllowed('editar')) {
                throw new HttpBadRequestException($request, 'Acesso negado');
            }
            $entity = $this->galeriaFotoRepository->getRepository()->find($uuid);
            if (empty($entity)) {
                throw new HttpNotFoundException($request, "Registro inválido.");
            }
        }
        $entity->hydrator($request->getParsedBody());

        $parametrosArquivo = $entity::getParametrosParaArquivo();

        /** @var UploadedFileInterface $foto */
        $foto = $request->getUploadedFiles()['foto'];
        if ($foto->getSize() < 0) {
            throw new HttpBadRequestException($request, sprintf(
                "Nenhum arquivo foi enviado. Selecione arquivos com até %sMB e dos tipos: %s",
                $parametrosArquivo->tamanhoArquivo,
                implode(", ", $parametrosArquivo->tipos)
            ));
        }

        $novasImagens = Imagem::redimencionar($foto, $parametrosArquivo);
        $entity->foto->set($novasImagens->dados->nomeNovo);
        $entity->foto_link->set($novasImagens->arquivos->value());

        if ($entity->id->value() > 0) {
            $registroEditado = $this->galeriaFotoRepository->getRepository()->update(['uuid' => $entity->uuid->value()], $entity->toSave('editar'));
            if ($registroEditado) {
                return self::responseJson($response, $entity->toApi());
            } else {
                throw new \Exception("Não foi possível editar o registro.");
            }
        } else {
            $novoRegistroId = $this->galeriaFotoRepository->getRepository()->insert($entity->toSave('novo'));

            if ($novoRegistroId) {
                $novosDados = $this->galeriaFotoRepository->getRepository()->find($novoRegistroId,'id');
                return self::responseJson($response, $novosDados->toApi());
            } else {
                throw new \Exception("Não foi possível criar o registro.");
            }
        }
    }

    public function ordem(Request $request, Response $response)
    {
        if (!$this->acl->isAllowed('ordem')) {
            throw new HttpBadRequestException($request, 'Acesso negado');
        }
        
        $galeriaEntity = $this->verificarRegistroGaleria($request->getQueryParams()['galeria'], $request);

        parse_str(urldecode($request->getBody()->getContents()), $listaRegistros);
        if (empty($listaRegistros)) {
            throw new HttpNotFoundException($request, "Lista de registros inválida.");
        }


        array_walk($listaRegistros['ordem'], function ($ordem, $uuid) {
            @$this->galeriaFotoRepository->getRepository()->update(['uuid' => $uuid, 'status' => 1], ['ordem' => $ordem]);
        });

        return self::responseJson($response, ['msg' => 'Ordem dos registros atualizada.']);
    }

    public function status($status, Request $request, Response $response)
    {
        if (!$this->acl->isAllowed('status')) {
            throw new HttpBadRequestException($request, 'Acesso negado');
        }

        $parametrosQuery          = $request->getQueryParams();
        $galeriaEntity = $this->verificarRegistroGaleria($parametrosQuery['galeria'], $request);
        $uuid = $parametrosQuery['uuid'];
        unset($parametrosQuery['galeria']);

        if (!empty($uuid)) {
            /** @var GaleriaFotoEntity $entity */
            $entityFoto = $this->galeriaFotoRepository->getRepository()->find($uuid);
        } else {
            throw new HttpNotFoundException($request, "Registro inválido;");
        }

        if (empty($entityFoto)) {
            throw new HttpNotFoundException($request, "Registro inválido;");
        }

        $novoStatus      = $status == 'a';
        $registroEditado = true;
        if (!$entityFoto->status->value() === $novoStatus) {
            $entityFoto->status->set((int)$novoStatus);
            $registroEditado = $this->galeriaFotoRepository->getRepository()->update(['uuid' => $entityFoto->uuid->value()], $entityFoto->toSave('status'));
        }

        if ($registroEditado) {
            return self::responseJson($response, $entityFoto->toApi());
        } else {
            throw new \Exception("Não foi possível alteara o status.");
        }
    }

    public function legenda($galeria, $id, Request $request, Response $response)
    {
        if (!$this->acl->isAllowed('legenda')) {
            throw new HttpBadRequestException($request, 'Acesso negado');
        }
        $parametrosQuery          = $request->getQueryParams();
        $galeriaEntity = $this->verificarRegistroGaleria($parametrosQuery['galeria'], $request);
        $uuid = $parametrosQuery['uuid'];
        unset($parametrosQuery['galeria']);

        if (!empty($uuid)) {
            /** @var GaleriaFotoEntity $entity */
            $entityFoto = $this->galeriaFotoRepository->getRepository()->find($uuid);
            if (empty($entityFoto)) {
                throw new HttpNotFoundException($request, "Registro inválido;");
            }
        } else {
            throw new HttpNotFoundException($request, "Registro inválido;");
        }
        $entityFoto->hydrator($request->getParsedBody());
        $registroEditado = $this->galeriaFotoRepository->getRepository()->update(['uuid' => $entityFoto->uuid->value()], $entityFoto->toSave('legenda'));

        if ($registroEditado) {
            return self::responseJson($response, $entityFoto->toApi());
        } else {
            throw new \Exception("Não foi possível alteara a legenda.");
        }
    }

    public function delete(Request $request, Response $response)
    {
        if (!$this->acl->isAllowed('delete')) {
            throw new HttpBadRequestException($request, 'Acesso negado');
        }
        $parametrosQuery          = $request->getQueryParams();
        $galeriaEntity = $this->verificarRegistroGaleria($parametrosQuery['galeria'], $request);
        $uuid = $parametrosQuery['uuid'];
        unset($parametrosQuery['galeria']);
        
        if (!empty($uuid)) {
            /** @var GaleriaFotoEntity $entityFoto */
            $entityFoto = $this->galeriaFotoRepository->getRepository()->find($uuid);
            if (empty($entityFoto)) {
                throw new HttpNotFoundException($request, "Registro fa foto é inválida;");
            }
        } else {
            throw new HttpNotFoundException($request, "Registro inválido;");
        }

        $apagar = $this->galeriaFotoRepository->getRepository()->delete(['uuid' => $entityFoto->uuid->value()]);

        if (!$apagar) {
            throw new \HttpException($request, "Não foi possível apagar o registro.");
        }

        return self::responseJson($response, ['removido' => $entityFoto->uuid->value()]);
    }

    /**
     * @param $galeriaUuid
     * @param ContainerInterface $container
     * @param Request $request
     * @return GaleriaEntity
     * @throws HttpNotFoundException
     * @throws \Doctrine\DBAL\Exception
     */
    protected function verificarRegistroGaleria($galeriaUuid, Request $request)
    {

        $parametrosQuery = ['where' => "uuid#{$galeriaUuid}"];
        if (!$this->acl->isAllowed('ler-todos')) {
            $parametrosQuery['where'] .= ',status#1';
        }
        /** @var GaleriaEntity $entity */
        $entity = FactoryFindBy::factory($this->galeriaRepository->getRepository(), $parametrosQuery)->getItem();

        if (empty($entity)) {
            throw new HttpNotFoundException($request, "Galeria inválida.");
        }
        return $entity;
    }
}
