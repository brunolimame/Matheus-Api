<?php

namespace Core\Lib;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;

class ValidacaoParseRequest
{
    static public function parse(Request $request, $tipoRequest = 'POST')
    {

        $requestDados = $tipoRequest == 'POST' ? $request->getParsedBody() : $request->getQueryParams();

        if (empty($requestDados)) {
            $requestDados = [];
        }
        $requestArquivos = $request->getUploadedFiles();
        array_walk($requestArquivos, function ($value, $key) use (&$requestDados) {
            if ($value instanceof UploadedFileInterface) {
                $requestDados[$key] = $value->getStream()->getMetadata('uri');
            }
        });
        return $requestDados;
    }
}
