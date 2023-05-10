<?php

namespace Modulo\Insignia\Controller;

use Boot\Provider\Jwt\JwtController;
use Core\Controller\BaseController;
use Core\Inferface\ParametrosArquivoInterface;
use Core\Lib\Arquivo\ArquivoNovosDados;
use Doctrine\DBAL\Exception;
use Modulo\Insignia\Entity\InsigniaEntity;
use Modulo\Insignia\Entity\UsuarioInsigniaEntity;
use Modulo\Insignia\Repository\InsigniaRepository;
use Modulo\Insignia\Repository\UsuarioInsigniaRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Exception\HttpBadRequestException;

class InsigniaController extends BaseController
{
    /** @var InsigniaRepository */
    public $insigniaRepository;
    /** @var JwtController */
    public $jwt;

    public function __construct(InsigniaRepository $insigniaRepository, JwtController $jwtController)
    {
        $this->insigniaRepository = $insigniaRepository;
        $this->jwt = $jwtController;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws \Exception
     */
    public function index(Request $request, Response $response): Response
    {
        $insignias = $this->insigniaRepository->getRepository()->findBy(['status' => 1], [], 5000)->getItens();

        $itens = [];

        /** @var InsigniaEntity $insignia */
        foreach ($insignias as $insignia) {
            array_push($itens, [
                'uuid' => $insignia->uuid->value(),
                'nome' => $insignia->nome->value(),
                'link' => $insignia->link->value(),
                'descricao' => $insignia->descricao->value()
            ]);
        }

        return self::responseJson($response, $itens);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param UsuarioInsigniaRepository $repoUsuarioInsignia
     * @return Response
     * @throws \Exception
     */
    public function getInsignias(Request $request, Response $response, UsuarioInsigniaRepository $repoUsuarioInsignia): Response
    {
        $argumentos = $request->getParsedBody();
        $usuario_insignias = $repoUsuarioInsignia->getRepository()->findBy(['usuario_uuid' => $argumentos['uuid']], [], 5000)->getItens();

        $itens = [];
        /** @var UsuarioInsigniaEntity $usuario_insignia */
        foreach ($usuario_insignias as $usuario_insignia) {
            /** @var InsigniaEntity $insignia */
            $insignia = $this->insigniaRepository->getRepository()->findBy(['uuid' => $usuario_insignia->insignia_uuid, 'status' => 1])->getItem();
            if ($insignia) {
                array_push($itens, [
                    'id' => $usuario_insignia->id->value(),
                    'uuid' => $insignia->uuid->value(),
                    'nome' => $insignia->nome->value(),
                    'link' => $insignia->link->value(),
                    'descricao' => $insignia->descricao->value(),
                    'titulo' => $usuario_insignia->titulo->value(),
                    'informacao' => $usuario_insignia->informacao->value()
                ]);
            }
        }

        return self::responseJson($response, $itens);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws Exception
     * @throws HttpBadRequestException
     */
    public function getInsignia(Request $request, Response $response): Response
    {
        $argumentos = $request->getParsedBody();

        /** @var InsigniaEntity $insignia */
        $insignia = $this->insigniaRepository->getRepository()->find($argumentos['uuid']);

        if ($insignia) {
            return self::responseJson($response, (object)[
                'nome' => $insignia->nome->value(),
                'descricao' => $insignia->descricao->value(),
                'link' => $insignia->link->value()
            ]);
        } else {
            throw new HttpBadRequestException($request, "Registro não encontrado.");
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws Exception
     * @throws HttpBadRequestException
     */
    public function create(Request $request, Response $response): Response
    {
        if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
            $argumentos = $request->getParsedBody();
            $uuid = $argumentos['uuid'];

            /** @var InsigniaEntity $insignia */
            $insignia = $this->insigniaRepository->getRepository()->find($uuid);

            if (empty($insignia)) {
                $insignia = $this->insigniaRepository->getRepository()::getEntity()::novaInsignia();
            }

            /** @var UploadedFileInterface $arquivo */
            $arquivo = current($request->getUploadedFiles());

            if ($arquivo) {
                $novosDados = ArquivoNovosDados::load($arquivo);
                /** @var ParametrosArquivoInterface $parametrosArquivo */
                $parametrosArquivo = $insignia::getParametrosParaArquivo();
                $arquivo->moveTo($parametrosArquivo->getLocalAbsoluto() . $novosDados->nomeNovo);
                $insignia->hydrator(['link' => $parametrosArquivo->getLocal() . $novosDados->nomeNovo]);
            }

            $insignia->hydrator($argumentos);

            if ($insignia->id->value() > 0) {
                $resultadoInsignia = $this->insigniaRepository->getRepository()->update(['uuid' => $insignia->uuid->value()], $insignia->toSave());
            } else {
                $resultadoInsignia = $this->insigniaRepository->getRepository()->insert($insignia->toSave());
            }

            if ($resultadoInsignia) {
                return self::responseJson($response, true);
            } else {
                throw new \Exception("Falha ao salvar.");
            }
        } else {
            throw new HttpBadRequestException($request, 'Acesso negado');
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param UsuarioInsigniaRepository $repoUsuarioInsignia
     * @return Response
     * @throws HttpBadRequestException
     */
    public function set(Request $request, Response $response, UsuarioInsigniaRepository $repoUsuarioInsignia): Response
    {
        if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
            $argumentos = $request->getParsedBody();

            $user = $this->jwt->decodeNovo($request);

            $usuario_insignia = $repoUsuarioInsignia->getRepository()::getEntity();
            $usuario_insignia->hydrator($argumentos);
            $usuario_insignia->hydrator(['informacao' => 'Concedido por ' . $user->nome . ' em ' . (new \DateTime())->format('d/m/Y')]);

            $resultadoInsert = $repoUsuarioInsignia->getRepository()->insert($usuario_insignia->toSave());

            if ($resultadoInsert) {
                return self::responseJson($response, true);
            } else {
                throw new \Exception("Falha ao salvar.");
            }
        } else {
            throw new HttpBadRequestException($request, 'Acesso negado');
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param UsuarioInsigniaRepository $repoUsuarioInsignia
     * @return Response
     * @throws HttpBadRequestException
     */
    public function delete(Request $request, Response $response, UsuarioInsigniaRepository $repoUsuarioInsignia): Response
    {
        if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
            $argumentos = $request->getParsedBody();
            $id = $argumentos['id'];

            $resultadoDelete = $repoUsuarioInsignia->getRepository()->delete(['id' => $id]);

            if ($resultadoDelete) {
                return self::responseJson($response, true);
            } else {
                throw new \Exception("Falha ao remover.");
            }
        } else {
            throw new HttpBadRequestException($request, 'Acesso negado');
        }
    }
}
