<?php

namespace Core\Lib\Arquivo;

use Core\Inferface\ParametrosArquivoInterface;
use PHPImageWorkshop\ImageWorkshop;
use Psr\Http\Message\UploadedFileInterface;

class Imagem
{
    const LISTA_QUALIDADE_TIPO = [
        'image/bmp'           => 80,
        'image/x-windows-bmp' => 80,
        'image/gif'           => 80,
        'image/jpeg'          => 80,
        'image/png'           => 8
    ];

    /**
     * @param UploadedFileInterface $imagem
     * @param ParametrosArquivoInterface $parametrosArquivo
     * @return ImagemDadosColection
     * @throws \PHPImageWorkshop\Core\Exception\ImageWorkshopLayerException
     * @throws \PHPImageWorkshop\Exception\ImageWorkshopException
     */
    static public function redimencionar(UploadedFileInterface $imagem, ParametrosArquivoInterface $parametrosArquivo)
    {
        $novosDados = ArquivoNovosDados::load($imagem);

        $imglib        = ImageWorkshop::initFromString($imagem->getStream());
        $listaArquivos = [];
        array_walk($parametrosArquivo->medidas, function ($medida) use ($novosDados, $imglib, $parametrosArquivo, &$listaArquivos) {
            $imgTemp = $imglib;
            [$lagura, $altura, $prefixo, $cortar] = $medida;
            $imgTemp->resize($imgTemp::UNIT_PIXEL, $lagura, $altura, !$cortar);
            $novoNome = empty($prefixo) ? $novosDados->nomeNovo : $prefixo . '_' . $novosDados->nomeNovo;
            $imgTemp->save($parametrosArquivo->getLocalAbsoluto(), $novoNome, true, null, $novosDados->qualidade);
            $listaArquivos[$prefixo] = $parametrosArquivo->getLocal() . '/' . $novoNome;
        });

        return ImagemDadosColection::load($novosDados, $listaArquivos);

    }
}