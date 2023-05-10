<?php

namespace Core\Lib;

use Core\BadRequestSerializadoException;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validation;

abstract class ValidarCampos
{
    static public function validar(ServerRequestInterface $request, Assert\Collection $regras, $dados, $grupo = null)
    {
        $validator = Validation::createValidator();
        $regras->allowExtraFields = true;
        
        $violations = $validator->validate($dados, $regras, $grupo);

        if ($violations->count() > 0) {
            $listaErros = [];
            foreach ($violations as $violacao) {
                $campo              = str_replace(['[', ']'], '', $violacao->getPropertyPath());
                $listaErros[$campo] = $violacao->getMessage();
            }
            throw new BadRequestSerializadoException($request, serialize($listaErros));
        }
    }
}