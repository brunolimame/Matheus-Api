<?php

namespace Modulo\Administrativo\Controller;

use Boot\Provider\Jwt\JwtController;
use Core\Controller\BaseController;
use Modulo\Administrativo\Entity\ConfigMesesEntity;
use Modulo\Administrativo\Repository\ConfigMesesRepository;
use Modulo\Cliente\Entity\ClienteEntity;
use Modulo\Cliente\Repository\ClienteRepository;
use Modulo\Post\Entity\PostEntity;
use Modulo\Post\Repository\PostRepository;
use Modulo\User\Entity\UserEntity;
use Modulo\User\Repository\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;

class AdministrativoController extends BaseController
{
    /** @var ConfigMesesRepository */
    public $configMesesRepository;
    /** @var JwtController */
    public $jwt;

    public function __construct(ConfigMesesRepository $configMesesRepository, JwtController $jwtController)
    {
        $this->configMesesRepository = $configMesesRepository;
        $this->jwt = $jwtController;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param ClienteRepository $repoCliente
     * @param UserRepository $repoUser
     * @return Response
     * @throws HttpBadRequestException
     */
    public function index(Request $request, Response $response, ClienteRepository $repoCliente, UserRepository $repoUser): Response
    {
        if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {

            $designers = $repoUser->findByDesigner()->getItens();
            $clientes = $repoCliente->getRepository()->findBy(['status' => 1], ['razao_social' => 'ASC'], 5000)->getItens();
            $postspdesigner = [];
            $clientespdesigner = [];

            /** @var UserEntity $designer */
            foreach ($designers as $designer) {
                $soma = 0;
                $somaCliente = 0;
                foreach ($clientes as $cliente) {
                    if ($designer->uuid->comparar($cliente->usuario_uuid)) {
                        $soma += $cliente->posts->value();
                        $somaCliente++;
                    }
                }
                array_push($postspdesigner, ['uuid' => $designer->uuid->value(), 'nome' => $designer->nome->value(), 'quantidade' => $soma]);
                array_push($clientespdesigner, ['uuid' => $designer->uuid->value(), 'nome' => $designer->nome->value(), 'quantidade' => $somaCliente]);
            }

            $soma = 0;
            /** @var ClienteEntity $cliente */
            foreach ($clientes as $cliente) {
                $soma += $cliente->posts->value();
            }
            array_push($postspdesigner, ['nome' => 'Total', 'quantidade' => $soma]);
            array_push($clientespdesigner, ['nome' => 'Total', 'quantidade' => count($clientes)]);
        } else {
            throw new HttpBadRequestException($request, 'Acesso negado');
        }
        return self::responseJson($response, (object)['postsdesigner' => $postspdesigner, 'clientesdesigner' => $clientespdesigner]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param ClienteRepository $repoCliente
     * @param UserRepository $repoUser
     * @return Response
     * @throws HttpBadRequestException
     */
    public function getMedia(Request $request, Response $response, ClienteRepository $repoCliente, UserRepository $repoUser): Response
    {
        if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
            $argumentos = $request->getParsedBody();
            $mes = $argumentos['mes'];
            $ano = $argumentos['ano'];

            switch ($mes) {
                case 'Janeiro':
                    $data = '01/' . $ano;
                    break;
                case 'Fevereiro':
                    $data = '02/' . $ano;
                    break;
                case 'Março':
                    $data = '03/' . $ano;
                    break;
                case 'Abril':
                    $data = '04/' . $ano;
                    break;
                case 'Maio':
                    $data = '05/' . $ano;
                    break;
                case 'Junho':
                    $data = '06/' . $ano;
                    break;
                case 'Julho':
                    $data = '07/' . $ano;
                    break;
                case 'Agosto':
                    $data = '08/' . $ano;
                    break;
                case 'Setembro':
                    $data = '09/' . $ano;
                    break;
                case 'Outubro':
                    $data = '10/' . $ano;
                    break;
                case 'Novembro':
                    $data = '11/' . $ano;
                    break;
                case 'Dezembro':
                    $data = '12/' . $ano;
                    break;
                default:
                    $data = '01/' . $ano;
            }

            $designers = $repoUser->findByDesigner()->getItens();
            $clientes = $repoCliente->getRepository()->findBy(['status' => 1], ['razao_social' => 'ASC'], 5000)->getItens();
            /** @var ConfigMesesEntity $dias */
            $dias = $this->configMesesRepository->getRepository()->findBy(['data' => $data])->getItem();
            $mediapdesigner = [];

            /** @var ClienteEntity $cliente */
            /** @var UserEntity $designer */
            foreach ($designers as $designer) {
                $soma = 0;
                foreach ($clientes as $cliente) {
                    if ($designer->uuid->comparar($cliente->usuario_uuid)) {
                        $soma += $cliente->posts->value();
                    }
                }
                array_push($mediapdesigner, ['uuid' => $designer->uuid->value(), 'nome' => $designer->nome->value(), 'quantidade' => number_format(($soma / $dias->dias->value()), 2, ',', '.')]);
            }
        } else {
            throw new HttpBadRequestException($request, 'Acesso negado');
        }
        return self::responseJson($response, (object)['mediadesigner' => $mediapdesigner]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param ClienteRepository $repoCliente
     * @param UserRepository $repoUser
     * @return Response
     * @throws HttpBadRequestException
     */
    public function indexMes(Request $request, Response $response, ClienteRepository $repoCliente, UserRepository $repoUser): Response
    {
        $meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
        $semana = ['DOMINGO', 'SEGUNDA', 'TERÇA', 'QUARTA', 'QUINTA', 'SEXTA', 'SÁBADO'];
        if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
            $argumentos = $request->getParsedBody();
            $cliente_uuid = $argumentos['cliente_uuid'];
            $mes = $argumentos['mes'];
            $ano = $argumentos['ano'];

            switch ($mes) {
                case 'Janeiro':
                    $mesNumero = '01';
                    $diaMax = '31';
                    break;
                case 'Fevereiro':
                    $mesNumero = '02';
                    $diaMax = '31';
                    break;
                case 'Março':
                    $mesNumero = '03';
                    $diaMax = '31';
                    break;
                case 'Abril':
                    $mesNumero = '04';
                    $diaMax = '30';
                    break;
                case 'Maio':
                    $mesNumero = '05';
                    $diaMax = '31';
                    break;
                case 'Junho':
                    $mesNumero = '06';
                    $diaMax = '30';
                    break;
                case 'Julho':
                    $mesNumero = '07';
                    $diaMax = '31';
                    break;
                case 'Agosto':
                    $mesNumero = '08';
                    $diaMax = '31';
                    break;
                case 'Setembro':
                    $mesNumero = '09';
                    $diaMax = '30';
                    break;
                case 'Outubro':
                    $mesNumero = '10';
                    $diaMax = '31';
                    break;
                case 'Novembro':
                    $mesNumero = '11';
                    $diaMax = '30';
                    break;
                case 'Dezembro':
                    $mesNumero = '12';
                    $diaMax = '31';
                    break;
                default:
                    $mesNumero = '01';
                    $diaMax = '01';
            }

            date_create_from_format('Y-m-d', $ano . $mesNumero . '01');
            date_create_from_format('Y-m-d', '');
//            date_diff();

            $designers = $repoUser->findByDesigner()->getItens();
            $clientes = $repoCliente->getRepository()->findBy(['status' => 1], ['razao_social' => 'ASC'], 5000)->getItens();
            $mediapdia = [];

            $soma = 0;

            /** @var UserEntity $designer */
            foreach ($designers as $designer) {
                $soma = 0;

                /** @var ClienteEntity $cliente */
                foreach ($clientes as $cliente) {
                    if ($designer->uuid->comparar($cliente->usuario_uuid)) {
                        $soma += $cliente->posts->value();
                    }
                }
                array_push($mediapdia, ['nome' => $designer->nome->value(), 'quantidade' => $soma]);
            }


        } else {
            throw new HttpBadRequestException($request, 'Acesso negado');
        }

        return self::responseJson($response, (object)['mediapdia' => $mediapdia]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws HttpBadRequestException
     */
    public function getMes(Request $request, Response $response): Response
    {
        if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
            $argumentos = $request->getParsedBody();
            $ano = $argumentos['ano'];

            $itens = $this->configMesesRepository->getRepository()->findBy([], [], 12, null, ['busca' => '/' . $ano, 'campos_busca' => ['data']])->getItens();
            $dias = [];
            /** @var ConfigMesesEntity $item */
            foreach ($itens as $item) {
                array_push($dias, $item->dias->value());
            }
        } else {
            throw new HttpBadRequestException($request, 'Acesso negado');
        }

        return self::responseJson($response, (object)['itens' => $dias]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws HttpBadRequestException
     */
    public function setMes(Request $request, Response $response): Response
    {
        if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
            $argumentos = $request->getParsedBody();

            $meses = [
                $argumentos['janeiro'],
                $argumentos['fevereiro'],
                $argumentos['marco'],
                $argumentos['abril'],
                $argumentos['maio'],
                $argumentos['junho'],
                $argumentos['julho'],
                $argumentos['agosto'],
                $argumentos['setembro'],
                $argumentos['outubro'],
                $argumentos['novembro'],
                $argumentos['dezembro'],
            ];

            $ano = $argumentos['ano'];

            /** @var ConfigMesesEntity $dado */
            for ($i = 0; $i < 12; $i++) {
                $mes = $i + 1;
                if ($mes < 10) {
                    $mes = '0' . $mes;
                }

                $dado = $this->configMesesRepository->getRepository()->findBy(['data' => $mes . '/' . $ano])->getItem();
                if (!$dado) {
                    $dado = $this->configMesesRepository->getRepository()::getEntity();
                    $dado->hydrator([
                        'data' => $mes . '/' . $ano,
                        'dias' => $meses[$i]
                    ]);
                    $this->configMesesRepository->getRepository()->insert($dado->toSave('novo'));
                } else {
                    $dado->dias->set($meses[$i]);
                    $this->configMesesRepository->getRepository()->update(['data' => $dado->data->value()], $dado->toSave('editar'));
                }
            }

        } else {
            throw new HttpBadRequestException($request, 'Acesso negado');
        }

        return self::responseJson($response, (object)['result' => true]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param ClienteRepository $repoCliente
     * @param UserRepository $repoUser
     * @param PostRepository $repoPost
     * @return Response
     * @throws HttpBadRequestException
     */
    public function getTabela(Request $request, Response $response, ClienteRepository $repoCliente, UserRepository $repoUser, PostRepository $repoPost): Response
    {
        $meses = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
        $diasSemana = ['DOM', 'SEG', 'TER', 'QUA', 'QUI', 'SEX', 'SÁB'];
        if ($this->jwt->validaAcesso($request, ['sadmin', 'admin'])) {
            $argumentos = $request->getParsedBody();

            $semana = $argumentos['semana'];
            $mes = $argumentos['mes'];
            $ano = $argumentos['ano'];

            switch ($mes) {
                case 'Janeiro':
                    $mesNumero = '01';
                    $maxDias = 31;
                    break;
                case 'Fevereiro':
                    $mesNumero = '02';
                    $maxDias = $this->isBissexto($ano) ? 29 : 28;
                    break;
                case 'Março':
                    $mesNumero = '03';
                    $maxDias = 31;
                    break;
                case 'Abril':
                    $mesNumero = '04';
                    $maxDias = 30;
                    break;
                case 'Maio':
                    $mesNumero = '05';
                    $maxDias = 31;
                    break;
                case 'Junho':
                    $mesNumero = '06';
                    $maxDias = 30;
                    break;
                case 'Julho':
                    $mesNumero = '07';
                    $maxDias = 31;
                    break;
                case 'Agosto':
                    $mesNumero = '08';
                    $maxDias = 31;
                    break;
                case 'Setembro':
                    $mesNumero = '09';
                    $maxDias = 30;
                    break;
                case 'Outubro':
                    $mesNumero = '10';
                    $maxDias = 31;
                    break;
                case 'Novembro':
                    $mesNumero = '11';
                    $maxDias = 30;
                    break;
                case 'Dezembro':
                    $mesNumero = '12';
                    $maxDias = 31;
                    break;
                default:
                    $mesNumero = '0';
                    $maxDias = 0;
                    throw new HttpBadRequestException($request, 'Falha ao obter o mês');
            }

            $clientes = $repoCliente->getRepository()->findBy(['status' => 1], ['razao_social' => 'ASC'], 5000)->getItens();

            $header = [];
            $body = [];

            /** @var PostEntity $post */
            /** @var UserEntity $designer */
            /** @var ClienteEntity $cliente */

            if ($semana == 'Semana 1') {
                $dataPrimeiro = date_format(date_create_from_format('Y-m-d', $ano . '-' . $mesNumero . '-01'), 'w');
                if ($dataPrimeiro != 0) {
                    for ($i = 0; $i < 7; $i++) {
                        if ($dataPrimeiro + $i > 6) {
                            $index = $dataPrimeiro + $i - 7;
                        } else {
                            $index = $dataPrimeiro + $i;
                        }
                        if ($dataPrimeiro + $i < 8) {
                            array_push($header, $diasSemana[$index] . ' - 0' . ($i + 1) . '/' . $mesNumero);
                        }
                    }

                    foreach ($clientes as $cliente) {
                        $designer = $repoUser->getRepository()->findBy(['uuid' => $cliente->usuario_uuid->value()])->getItem();
                        $tabelaSemana = [];
                        for ($i = 0; $i < 7; $i++) {
                            if ($dataPrimeiro + $i < 8) {
                                $post = $repoPost->getRepository()->findBy(['cliente_uuid' => $cliente->uuid->value(), 'data' => $ano . '-' . $mesNumero . '-0' . $i])->getItem();
                                if ($post) {
                                    array_push($tabelaSemana, $designer->nome->value());
                                } else {
                                    array_push($tabelaSemana, '');
                                }
                            }
                        }

                        $size = count($tabelaSemana);
                        for ($i = $size; $i > 0; $i--) {
                            $tabelaSemana[$i] = $tabelaSemana[$i - 1];
                        }

                        $tabelaSemana[0] = $cliente->razao_social->value();
                        array_push($body, $tabelaSemana);
                    }
                } else {
                    array_push($header, $diasSemana[$dataPrimeiro] . ' - 01/' . $mesNumero);

                    foreach ($clientes as $cliente) {
                        $designer = $repoUser->getRepository()->findBy(['uuid' => $cliente->usuario_uuid->value()])->getItem();
                        $tabelaSemana = [];

                        $post = $repoPost->getRepository()->findBy(['cliente_uuid' => $cliente->uuid->value(), 'data' => $ano . '-' . $mesNumero . '-01'])->getItem();
                        if ($post) {
                            array_push($tabelaSemana, $designer->nome->value());
                        } else {
                            array_push($tabelaSemana, '');
                        }

                        $size = count($tabelaSemana);
                        for ($i = $size; $i > 0; $i--) {
                            $tabelaSemana[$i] = $tabelaSemana[$i - 1];
                        }

                        $tabelaSemana[0] = $cliente->razao_social->value();
                        array_push($body, $tabelaSemana);
                    }
                }
            } elseif ($semana == 'Semana 2') {
                $diaMes = 1;
                do {
                    $diaMes++;
                } while (date_format(date_create_from_format('Y-m-d', $ano . '-' . $mesNumero . '-' . ($diaMes < 10 ? '0' . $diaMes : $diaMes)), 'w') != 0);

                $dataPrimeiro = 1;
                for ($i = 0; $i < 7; $i++) {
                    if ($dataPrimeiro + $i > 6) {
                        $index = $dataPrimeiro + $i - 7;
                    } else {
                        $index = $dataPrimeiro + $i;
                    }
                    array_push($header, $diasSemana[$index] . ' - ' . ((($i + $diaMes + 1) < 10) ? '0' . ($i + $diaMes + 1) : ($i + $diaMes + 1)) . '/' . $mesNumero);
                }

                foreach ($clientes as $cliente) {
                    $designer = $repoUser->getRepository()->findBy(['uuid' => $cliente->usuario_uuid->value()])->getItem();
                    $tabelaSemana = [];

                    for ($i = $diaMes + 1; $i < ($diaMes + 8); $i++) {
                        $post = $repoPost->getRepository()->findBy(['cliente_uuid' => $cliente->uuid->value(), 'data' => $ano . '-' . $mesNumero . '-' . ($i < 10 ? '0' . $i : $i)])->getItem();

                        if ($post) {
                            array_push($tabelaSemana, $designer->nome->value());
                        } else {
                            array_push($tabelaSemana, '');
                        }
                    }

                    array_push($body, [
                        $cliente->razao_social->value(),
                        $tabelaSemana[0],
                        $tabelaSemana[1],
                        $tabelaSemana[2],
                        $tabelaSemana[3],
                        $tabelaSemana[4],
                        $tabelaSemana[5],
                        $tabelaSemana[6]
                    ]);
                }

            } elseif ($semana == 'Semana 3') {
                $diaMes = 1;
                $count = 0;
                while (!$pararWhile) {
                    $diaSemana = \DateTime::createFromFormat("Y-m-d", "{$ano}-{$mesNumero}-{$diaMes}")->format("w");
                    $diaMes++;
                    if ($diaSemana == 0) {
                        $count++;
                    }
                    $pararWhile = ($diaSemana == 0 && $count == 2);
                }

                $dataPrimeiro = 1;
                for ($i = 0; $i < 7; $i++) {
                    if ($dataPrimeiro + $i > 6) {
                        $index = $dataPrimeiro + $i - 7;
                    } else {
                        $index = $dataPrimeiro + $i;
                    }
                    array_push($header, $diasSemana[$index] . ' - ' . ((($i + $diaMes) < 10) ? '0' . ($i + $diaMes) : ($i + $diaMes)) . '/' . $mesNumero);
                }

                foreach ($clientes as $cliente) {
                    $designer = $repoUser->getRepository()->findBy(['uuid' => $cliente->usuario_uuid->value()])->getItem();
                    $tabelaSemana = [];

                    for ($i = $diaMes; $i < ($diaMes + 7); $i++) {
                        $post = $repoPost->getRepository()->findBy(['cliente_uuid' => $cliente->uuid->value(), 'data' => $ano . '-' . $mesNumero . '-' . ($i < 10 ? '0' . $i : $i)])->getItem();

                        if ($post) {
                            array_push($tabelaSemana, $designer->nome->value());
                        } else {
                            array_push($tabelaSemana, '');
                        }
                    }

                    array_push($body, [
                        $cliente->razao_social->value(),
                        $tabelaSemana[0],
                        $tabelaSemana[1],
                        $tabelaSemana[2],
                        $tabelaSemana[3],
                        $tabelaSemana[4],
                        $tabelaSemana[5],
                        $tabelaSemana[6]
                    ]);
                }
            } elseif ($semana == 'Semana 4') {
                $diaMes = 1;
                $count = 0;
                while (!$pararWhile) {
                    $diaSemana = \DateTime::createFromFormat("Y-m-d", "{$ano}-{$mesNumero}-{$diaMes}")->format("w");
                    $diaMes++;
                    if ($diaSemana == 0) {
                        $count++;
                    }
                    $pararWhile = ($diaSemana == 0 && $count == 3);
                }

                $dataPrimeiro = 1;
                for ($i = 0; $i < 7; $i++) {
                    if ($dataPrimeiro + $i > 6) {
                        $index = $dataPrimeiro + $i - 7;
                    } else {
                        $index = $dataPrimeiro + $i;
                    }
                    array_push($header, $diasSemana[$index] . ' - ' . ((($i + $diaMes) < 10) ? '0' . ($i + $diaMes) : ($i + $diaMes)) . '/' . $mesNumero);
                }

                foreach ($clientes as $cliente) {
                    $designer = $repoUser->getRepository()->findBy(['uuid' => $cliente->usuario_uuid->value()])->getItem();
                    $tabelaSemana = [];

                    for ($i = $diaMes; $i < ($diaMes + 7); $i++) {
                        $post = $repoPost->getRepository()->findBy(['cliente_uuid' => $cliente->uuid->value(), 'data' => $ano . '-' . $mesNumero . '-' . ($i < 10 ? '0' . $i : $i)])->getItem();

                        if ($post) {
                            array_push($tabelaSemana, $designer->nome->value());
                        } else {
                            array_push($tabelaSemana, '');
                        }
                    }

                    array_push($body, [
                        $cliente->razao_social->value(),
                        $tabelaSemana[0],
                        $tabelaSemana[1],
                        $tabelaSemana[2],
                        $tabelaSemana[3],
                        $tabelaSemana[4],
                        $tabelaSemana[5],
                        $tabelaSemana[6]
                    ]);
                }

            } elseif ($semana == 'Semana 5') {
                $diaMes = 1;
                $count = 0;
                while (!$pararWhile) {
                    $diaSemana = \DateTime::createFromFormat("Y-m-d", "{$ano}-{$mesNumero}-{$diaMes}")->format("w");
                    $diaMes++;
                    if ($diaSemana == 0) {
                        $count++;
                    }
                    $pararWhile = ($diaSemana == 0 && $count == 4);
                }

                $dataPrimeiro = 1;
                for ($i = 0; $i <= ($maxDias - $diaMes); $i++) {
                    if ($dataPrimeiro + $i > 6) {
                        $index = $dataPrimeiro + $i - 7;
                    } else {
                        $index = $dataPrimeiro + $i;
                    }
                    array_push($header, $diasSemana[$index] . ' - ' . ($i + $diaMes) . '/' . $mesNumero);
                }

                foreach ($clientes as $cliente) {
                    $designer = $repoUser->getRepository()->findBy(['uuid' => $cliente->usuario_uuid->value()])->getItem();
                    $tabelaSemana = [];
                    for ($i = $diaMes; $i <= $maxDias; $i++) {
                        $post = $repoPost->getRepository()->findBy(['cliente_uuid' => $cliente->uuid->value(), 'data' => $ano . '-' . $mesNumero . '-' . $i])->getItem();
                        if ($post) {
                            array_push($tabelaSemana, $designer->nome->value());
                        } else {
                            array_push($tabelaSemana, '');
                        }
                    }

                    $size = count($tabelaSemana);
                    for ($i = $size; $i > 0; $i--) {
                        $tabelaSemana[$i] = $tabelaSemana[$i - 1];
                    }

                    $tabelaSemana[0] = $cliente->razao_social->value();
                    array_push($body, $tabelaSemana);
                }
            } else {
                throw new HttpBadRequestException($request, 'Falha ao obter a semana');
            }
        } else {
            throw new HttpBadRequestException($request, 'Acesso negado');
        }

        return self::responseJson($response, (object)['header' => $header, 'body' => $body]);
    }

    public function isBissexto(int $ano)
    {
        return (($ano % 4 == 0) && (($ano % 100 != 0) || ($ano % 400 == 0)));
    }
}
