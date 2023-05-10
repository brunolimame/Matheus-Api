<?php

namespace Core\Lib;

use App\src\Core\Inferface\LimiteArquivoInterface;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Exception\HttpNotFoundException;

class LimiteArquivo implements LimiteArquivoInterface
{
    public $caminhoSalvo;
    public $localSalvo;
    public $tipos   = [];
    public $tamanhoArquivo;
    public $medidas = [];

    static public function load(string $localSalvo, int $limiteArquivo, array $medidas = [], array $tipos = []):LimiteArquivoInterface
    {
        $self = new static();
        return $self
            ->setLocal($localSalvo)
            ->setTipo($tipos)
            ->setTamanhoMaximoDoArquivo($limiteArquivo)
            ->setMedidas($medidas);
    }

    public function getTamanho()
    {
        return $this->tamanhoArquivo;
    }

    public function setLocal(string $local)
    {
        $this->caminhoSalvo = $_SERVER['DOCUMENT_ROOT'] . $local;
        $this->localSalvo   = $local;
        return $this;
    }

    public function getLocal()
    {
        return $this->localSalvo;
    }

    public function getLocalAbsoluto()
    {
        return $this->caminhoSalvo;
    }

    public function setTipo(array $tipos = [])
    {
        if (!empty($tipos) && is_array($tipos)) {
            $this->tipos = $tipos;
        }

        return $this;
    }

    public function getTipo()
    {
        return $this->tipos;
    }

    public function addTipo(string $tipo)
    {
        if (!is_array($this->tipos)) {
            $this->tipos = [];
        }
        array_push($this->tipos, $tipo);
        return $this;
    }


    public function verificarTipoArquivo(UploadedFileInterface $arquivo):bool
    {
        if (empty($this->tipos)) {
            return true;
        }
        return in_array($arquivo->getClientMediaType(), $this->tipos);
    }

    public function setTamanhoMaximoDoArquivo($limiteEmMB)
    {
        $this->tamanhoArquivo = (int)$limiteEmMB;
        return $this;
    }

    public function verificarLimiteArquivo(UploadedFileInterface $arquivo):bool
    {
        return ($arquivo->getSize() / 1024 / 1024) < $this->tamanhoArquivo;
    }

    public function setMedidas($medidas = [])
    {
        if (is_array($medidas)) {
            $this->medidas = array_reduce($medidas, function ($result, $item) {
                if (empty($item[0]) || !is_int($item[0])) {
                    throw new \Exception("Largura em pixel inválida ou não informada", 401);
                }
                if (empty($item[1]) || !is_int($item[1])) {
                    throw new \Exception("Altura em pixel inválida ou não informada", 401);
                }
                if (empty($item[2])) {
                    $item[2] = null;
                }
                if (empty($item[3])) {
                    $item[3] = false;
                }
                $result[] = $item;
                return $result;
            });

        }
        return $this;
    }

    public function getMedidas()
    {
        return $this->medidas;
    }

    /**
     * @param int $largura
     * @param int $altura
     * @param null $prefixo
     * @param bool $cortarImagem
     * @return $this
     */
    public function addMedida(int $largura, int $altura, $prefixo = null, bool $cortarImagem = false)
    {
        if (!is_array($this->medidas)) {
            $this->medidas = [];
        }
        array_push($this->medidas, [$largura, $altura, $prefixo, $cortarImagem]);
        return $this;
    }
}