<?php

namespace Modulo\Usuario\Validator;

abstract class UsuarioForcaSenhaValidator
{
    static public function validarForcaSenha($senha=null)
    {
        $verificarNumero = preg_match("%([0-9])%", $senha) ? true : false;
        $verificarLetraMaiuscula  = preg_match("%([A-Z])%", $senha) ? true : false;
        $verificarLetraMinuscula = preg_match("%([a-z])%", $senha) ? true : false;

        return $verificarNumero and $verificarLetraMaiuscula and $verificarLetraMinuscula;
    }
}
