<?php


namespace Core\Entity\Value;


class EntityValueInteiro implements EntityValueObjectInterface
{
    private $valor;

    public function __construct($valor = null)
    {
        $this->set($valor);
    }

    public static function factory($valor = null)
    {
        return new self($valor);
    }

    public function set($valor)
    {
        $this->valor = intval($valor);
        return $this;
    }

    public function value()
    {
        return $this->valor;
    }


    public function __toString()
    {
        return empty($this->value()) ? "" : $this->value();
    }
}
