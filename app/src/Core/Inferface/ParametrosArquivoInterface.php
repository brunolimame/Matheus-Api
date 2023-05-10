<?php

namespace Core\Inferface;

use Psr\Http\Message\UploadedFileInterface;

interface ParametrosArquivoInterface
{
    static public function load(string $localSalvo, int $limiteArquivo, array $medidas = [], array $tipos = []):ParametrosArquivoInterface;

    public function getTamanho();

    public function setLocal(string $local);

    public function getLocal();

    public function getLocalAbsoluto();

    public function setTipo(array $tipos = []);

    public function addTipo(string $tipo);

    public function getTipo();

    public function verificarTipoArquivo(UploadedFileInterface $arquivo):bool;

    public function setTamanhoMaximoDoArquivo($limiteEmMB);

    public function verificarLimiteArquivo(UploadedFileInterface $arquivo):bool;

    public function setMedidas(array $medidas = []);

    public function addMedida(int $largura, int $altura, $prefixo = null, bool $cortarImagem = false);

    public function getMedidas();

}