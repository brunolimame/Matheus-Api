<?php

namespace Boot\Provider\Jwt;

use Exception;
use Firebase\JWT\JWT;
use Boot\Provider\JwtProvider;
use Core\Repository\FactoryFindBy;
use Boot\Provider\PHPmailerProvider;
use Psr\Container\ContainerInterface;
use Modulo\Usuario\Entity\UsuarioEntity;
use Slim\Exception\HttpNotFoundException;
use Boot\Provider\Email\EnvioEmailInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Modulo\Usuario\Provider\UsuarioViewProvider;
use Modulo\Usuario\Repository\UsuarioRepository;
use Boot\Provider\Email\PHPmailer\PHPmailerMessage;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class JwtController
{
    const TOKEN_PUBLIC = 'public';
    const TOKEN_REFRESH = 'refresh';
    const TOKEN_PRIVATE = 'private';

    protected $jwtConf;
    /**@var UsuarioRepository */
    protected $usuarioRepository;
    
    public function __construct(ContainerInterface $container, UsuarioRepository $usuarioRepository)
    {
        $this->jwtConf = $container->get(JwtProvider::JWT_CONF);
        $this->usuarioRepository = $usuarioRepository;
    }


    public function auth(Request $request, Response $response)
    {

        try {
            $issued = time();

            $payload = [
                'type' => self::TOKEN_PUBLIC,
                'rand' => md5(random_bytes(8)),
                'nivel' => 'convidado',
                'iat'  => $issued,
                'exp'  => ($issued + $this->jwtConf->life_public) * 100
            ];

            $token = JWT::encode($payload, $this->jwtConf->secret, $this->jwtConf->algorithm);

            $newPayload = [
                'token' => $token,
                'life'  => $this->jwtConf->life_public
            ];

            return $this->jsonResponse($response, $newPayload);
        } catch (Exception $e) {
            throw new \InvalidArgumentException("Token inválido", 500);
        }
    }

    public function validarTokens(Request $request, Response $response)
    {
        $tokens             = $this->getTokensRequest($request);
        $tokenValido        = null;
        $refreshTokenValido = null;
        try {
            if (!empty($tokens->token)) {
                $tokenValido = !empty(JWT::decode($tokens->token, $this->jwtConf->secret, [$this->jwtConf->algorithm]));
            }
        } catch (Exception $e) {
            $tokenValido = false;
        }
        try {
            if (!empty($tokens->refresh)) {
                $refreshTokenValido = !empty(JWT::decode($tokens->refresh, $this->jwtConf->secret_refresh, [$this->jwtConf->algorithm]));
            }
        } catch (Exception $e) {
            $refreshTokenValido = false;
        }

        return $this->jsonResponse($response, array_filter([
            'token'   => $tokenValido,
            'refresh' => $refreshTokenValido
        ]));
    }

    protected function factoryToken(UsuarioEntity $usuario)
    {
        $issued = time();

        $payloadToken = [
            'type' => self::TOKEN_PRIVATE,
            'iat'  => $issued,
            'exp'  => $issued + $this->jwtConf->life,
            'uuid' => $usuario->uuid->value(),
            'nivel' => $usuario->nivel->value(),
            'nome' => $usuario->nome->value(),
            'foto' => $usuario->foto->value(),
        ];

        $newToken = JWT::encode($payloadToken, $this->jwtConf->secret, $this->jwtConf->algorithm);

        $payloadTokenRefresh = [
            'type' => self::TOKEN_REFRESH,
            'iat'  => $issued,
            'exp'  => $issued + $this->jwtConf->life_refresh,
            'token'  => [
                'type' => self::TOKEN_PRIVATE,
                'uuid'  => $usuario->uuid->value()
            ]
        ];

        $refreshToken = JWT::encode($payloadTokenRefresh, $this->jwtConf->secret_refresh, $this->jwtConf->algorithm);

        return [
            'token'        => $newToken,
            'life'         => $this->jwtConf->life,
            'refresh'      => $refreshToken,
            'life_refresh' => $this->jwtConf->life_refresh
        ];
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws Exception
     */
    public function login(Request $request, Response $response): Response
    {
        $dataReq = $request->getParsedBody();

        /** @var UsuarioEntity $usuarioEntity */
        $usuarioEntity = $this->usuarioRepository->findByUsernameEmail($dataReq['_username']);

        if (empty($usuarioEntity) ||
            !$usuarioEntity->isValidPass($dataReq['_password'], $usuarioEntity->salt->value(), $usuarioEntity->password->value())
        ) {
            throw new Exception("Usuário ou senha inválido(s)", 500);
        }

        try {
            $resultJson = $this->factoryToken($usuarioEntity);

            return $this->jsonResponse($response, $resultJson);
        } catch (Exception $e) {
            throw new Exception("Erro ao gerar token", 500);
        }
    }
    

    public function refresh(Request $request, Response $response)
    {
        $refreshToken = current($request->getHeader($this->jwtConf->header));
        if (empty($refreshToken)) {
            throw new \InvalidArgumentException("Token não informado", 401);
        }

        try {
            $decoded = (array)JWT::decode($refreshToken, $this->jwtConf->secret_refresh, [$this->jwtConf->algorithm]);

            if ($decoded['type'] != self::TOKEN_REFRESH) {
                throw new \InvalidArgumentException("Token inválido", 401);
            };

            $usuarioEntity = FactoryFindBy::factory($this->usuarioRepository, [
                'select' => 'uuid,nome,foto,username,email,password,salt,nivel',
                'where' => 'uuid#' . $decoded['token']->uuid
            ], 1)->getItem();

            if (empty($usuarioEntity)) {
                throw new HttpNotFoundException($request, "Erro na geração do novo Token. Usuário não encontrado.");
            }

            $resultJson = $this->factoryToken($usuarioEntity);

            return $this->jsonResponse($response, $resultJson);
        } catch (Exception $e) {
            throw new Exception("Erro na revalidação do token.", 500);
        }
    }

    public function decode(Request $request, Response $response)
    {
        $token = !empty($cookieParams[JwtProvider::JWT_KEY]) ? $cookieParams[JwtProvider::JWT_KEY] : current($request->getHeader(JwtProvider::JWT_HEADER));

        if (empty($token)) {
            throw new \InvalidArgumentException("Token não informado");
        }

        $decodeToken = JWT::decode($token, $this->jwtConf->secret, [$this->jwtConf->algorithm]);

        if (empty($decodeToken)) {
            throw new \InvalidArgumentException("Token inválido");
        }

        return $this->jsonResponse($response, ['decode' => $decodeToken]);
    }

    public function decodeNovo(Request $request)
    {
        $token = !empty($cookieParams[JwtProvider::JWT_KEY]) ? $cookieParams[JwtProvider::JWT_KEY] : current($request->getHeader(JwtProvider::JWT_HEADER));
        if (empty($token)) {
            throw new \InvalidArgumentException("Token não informado");
        }
        $decodeToken = JWT::decode($token, $this->jwtConf->secret, [$this->jwtConf->algorithm]);
        if (empty($decodeToken)) {
            throw new \InvalidArgumentException("Token inválido");
        }
        $decodeToken->nivel = explode(',',$decodeToken->nivel);
        return $decodeToken;
    }

    public function validaAcesso(Request $request, array $niveis) {
        $decode = $this->decodeNovo($request);
        foreach ($niveis as $nivel) {
            foreach ($decode->nivel as $decode_nivel)
            if ($decode_nivel == $nivel) {
                return true;
            }
        }
        return false;
    }

    protected function getTokensRequest(Request $request)
    {
        $cookieParams = $request->getCookieParams();
        $token        = !empty($cookieParams[JwtProvider::JWT_KEY]) ? $cookieParams[JwtProvider::JWT_KEY] : current($request->getHeader(JwtProvider::JWT_HEADER));
        $refresh      = !empty($cookieParams[JwtProvider::JWT_KEY_REFRESH]) ? $cookieParams[JwtProvider::JWT_KEY_REFRESH] : current($request->getHeader(JwtProvider::JWT_HEADER_REFRESH));

        if (empty($token) && empty($refresh)) {
            throw new \InvalidArgumentException("Token não informado");
        }
        return (object)[
            'token'   => $token,
            'refresh' => $refresh
        ];
    }

    protected function jsonResponse(Response &$response, $data = [])
    {
        $payload = json_encode($data, JSON_PRETTY_PRINT);
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}
