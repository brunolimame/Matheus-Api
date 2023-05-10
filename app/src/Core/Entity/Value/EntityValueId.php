<?php


namespace Core\Entity\Value;


class EntityValueId implements EntityValueObjectInterface
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
        $this->valor = !is_null($valor) ? (int)$valor : null;
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
