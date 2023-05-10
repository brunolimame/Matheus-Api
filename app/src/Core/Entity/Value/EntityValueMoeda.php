<?php


namespace Core\Entity\Value;


class EntityValueMoeda implements EntityValueObjectInterface
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
        $valorTratado = preg_replace('/[^0-9]/', '', $valor);
        $this->valor = !empty($valorTratado) ? intval($valorTratado)/100 : 0.0;
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
