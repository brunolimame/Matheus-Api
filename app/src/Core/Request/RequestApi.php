<?php

namespace Core\Request;

use GuzzleHttp\Client;
use Boot\Provider\JwtProvider;
use GuzzleHttp\Exception\GuzzleException;

class RequestApi
{
    const TOKEN_CONVIDADO = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzM4NCJ9.eyJ0eXBlIjoicHVibGljIiwicmFuZCI6IjgxN2ZmZjE3NDgxM2YzMjBjZTI4ZGFjMTEwNWRmMTdkIiwibml2ZWwiOiJjb252aWRhZG8iLCJpYXQiOjE2NDI3MDM0ODEsImV4cCI6MTY1MjcwMzQ4MDAwfQ.Jjo4tBVr5er0hcl1neymxM-bMe1BCcaK5Ka9pxSQA4B-Eh-idlmw-PrIxYD4wWm5";
    const TOKEN_LOGADO = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzM4NCJ9.eyJ0eXBlIjoicHJpdmF0ZSIsImlhdCI6MTY0MjcwMzI4OCwiZXhwIjoxNjUyNzAzMjg3LCJ1dWlkIjoiYzU0OTFkODUtMGMzYi00ZmExLWFkYTAtZmFjOGUxNTYxMmIzIiwibml2ZWwiOiJzYWRtaW4iLCJub21lIjoiSVR3ZWIiLCJmb3RvIjoiYXZhdGFyLWl0d2ViXzM0OTc0N2I5OGRlNDBjZTNlNzQ0YmQuanBnIn0.rTGOP9q_rEfC73RDwDuvPsMVC_xhXIZZwI8h-pPx_F5udlfhxBYNZUi_WIxkhDxH";
    const TOKEN_REFRESH =  "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzM4NCJ9.eyJ0eXBlIjoicmVmcmVzaCIsImlhdCI6MTY0MjcwMzI4OCwiZXhwIjoxNjUyNzAzMjg3LCJ0b2tlbiI6eyJ0eXBlIjoicHJpdmF0ZSIsInV1aWQiOiJjNTQ5MWQ4NS0wYzNiLTRmYTEtYWRhMC1mYWM4ZTE1NjEyYjMifX0.tiFkqufnJDh3ys5jjpMnH1vgO6k6ErEXBRiY-zMJtPF2eIMqv4C3ZZUCEoPc6bDH";

    public $host;
    public $https = true;
    public $validarCertificado = false;
    public $metodo = 'GET';
    public $parametros = [];
    public $endpoint = [];
    public $arquivos = [];
    public $conexao;
    public $timeout = 60;
    public $token;
    public $header = [];

    public function __construct($host = null, $https = false)
    {
        $https ? $this->setHttps() : $this->setHttp();

        $this->setHost($host)
            ->addHeader("Accept", "application/json");
    }

    static function factory($metodo = 'GET', $parametros = []): RequestApi
    {
        /** @var RequestApi $request */
        $request = new self($_SERVER['HTTP_HOST'], false);
        $request->metodo = $metodo;
        $request->metodo == 'GET' ? $request->setGet() : $request->setPost();

        if (!empty($parametros)) {
            $request->addParametros($parametros);
        }

        return $request;
    }

    public function addHeader($key, $value)
    {
        $this->header[$key] = $value;
        return $this;
    }

    public function removerHeader($key)
    {
        unset($this->header[$key]);
        return $this;
    }


    public function setToken($token)
    {
        $this->addHeader('X-Token', $token);
        return $this;
    }

    public function setHttps()
    {
        $this->https = true;
        return $this;
    }

    public function setHttp()
    {
        $this->https = false;
        return $this;
    }

    public function setHost($host = null)
    {
        $this->host = !empty($host) ? $host : $_SERVER['HTTP_HOST'];
        return $this;
    }

    public function getHost($endpoint = null)
    {
        $protocolo = $this->https ? 'https' : 'http';
        return $protocolo . '://' . $this->host . $endpoint;
    }

    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    public function getEndpoint($addEndpoint = null)
    {
        return $this->endpoint . $addEndpoint;
    }

    public function addEndpoint($addEndpoint)
    {
        $this->endpoint.= $addEndpoint;
        return $this;
    }

    public function setTimeOut($tempo = 30)
    {
        $this->timeout = $tempo;
        return $this;
    }

    public function setGet()
    {
        $this->metodo = 'GET';
        return $this;
    }

    public function setPost()
    {
        $this->metodo = 'POST';
        return $this;
    }

    public function ativarValidacaoCertificado()
    {
        $this->validarCertificado = true;
        return $this;
    }

    public function desabilitarValidacaoCertificado()
    {
        $this->validarCertificado = false;
        return $this;
    }

    public function addParametros($parametros = [])
    {
        if (!empty($parametros) && is_array($parametros)) {
            array_walk($parametros, function ($valor, $key) {
                $this->parametros[$key] = $valor;
            });
        }

        return $this;
    }

    public function addParametro($key, $valor = null)
    {
        $this->parametros[$key] = $valor;
        return $this;
    }

    public function addSelect($campos = [])
    {
        !empty($campos) ? $this->addParametro('select', $campos) : $this->removerParametro('select');
        return $this;
    }

    public function addWhere($campos = [])
    {
        !empty($campos) ? $this->addParametro('where', $campos) : $this->removerParametro('where');
        return $this;
    }

    public function removerParametro($key)
    {
        unset($this->parametros[$key]);
        return $this;
    }

    public function addArquivo($key, $nomeArquivo, $conteudo, $header = [])
    {
        $dadosDoArquivo = [
            'name'     => $key,
            'contents' => $conteudo,
            'filename' => $nomeArquivo,
        ];

        if (!empty($header)) {
            $dadosDoArquivo['headers'] = $header;
        }
        $this->arquivos[$key] = $dadosDoArquivo;

        return $this;
    }

    public function removerArquivo($key)
    {
        unset($this->arquivos[$key]);
        return $this;
    }

    public function exec()
    {

        try {
            $token = $_SESSION[JwtProvider::JWT_HEADER];
            if (empty($this->token)) {
                if (!empty($token)) {
                    $this->setToken($token);
                }
            }

            $request = new Client(['base_uri' => $this->getHost()]);

            $requenstVar = [
                'headers' => $this->header,
                'verify' => $this->validarCertificado,
            ];
            $addEndpoint = null;
            if (!empty($this->parametros)) {
                if ($this->metodo == 'post') {
                    if (empty($this->arquivos)) {
                        unset($requenstVar['multipart']);
                        $requenstVar['form_params'] = $this->parametros;
                    } else {
                        unset($requenstVar['form_params']);
                        array_walk($this->parametros, function ($valor, $key) use (&$requenstVar) {
                            $requenstVar['multipart'][] = [
                                'name' => $key,
                                'contents' => $valor
                            ];
                        });
                        array_walk($this->arquivos, function ($valor) use (&$requenstVar) {
                            $requenstVar['multipart'][] = $valor;
                        });
                    }
                } else {
                    $addEndpoint = '?' . http_build_query($this->parametros);
                }
            }


            $response = $request->request($this->metodo, $this->getEndpoint($addEndpoint), $requenstVar);
            $responseData = json_decode($response->getBody()->getContents());
            return $responseData;
        } catch (GuzzleException $e) {
            $responseEx = $e->getResponse();
            $responseBodyAsString = $responseEx->getBody()->getContents();
            return json_decode($responseBodyAsString);
        }
    }
}
