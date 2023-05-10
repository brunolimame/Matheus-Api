<?php


namespace Core\Entity\Value;


use Respect\Validation\Validator;

class EntityValueDate extends EntityValueDatetime
{
    public static function factory($valor = null)
    {
        return new self($valor,self::DATE_FORMAT);
    }


    protected function validarFormato($formato = null)
    {
        return (is_string($formato) && !empty($formato) && !is_null($formato)) ? $formato : self::DATE_FORMAT;
    }

    protected function validarFormatoBr($formato = null)
    {
        return (is_string($formato) && !empty($formato) && !is_null($formato)) ? $formato : self::DATE_FORMAT_BR;
    }

}