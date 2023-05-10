<?php

namespace Core\Lib;

use Symfony\Component\Validator\Constraints\Collection;

class ValidacaoCollection
{

    /**
     * @param null $options
     * @return Collection
     */
    static public function load($options = null):Collection
    {
        $colection                       = new Collection($options);
        $colection->extraFieldsMessage   = 'Campo não experado.';
        $colection->missingFieldsMessage = 'Campo não definido.';
        return $colection;
    }
}