<?php


namespace Core\Entity\Value;

class EntityValueArray implements EntityValueObjectInterface
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
        $this->valor = $valor;
        if (is_array($valor)) {
            $this->valor = implode(",", $valor);
        }
        return $this;
    }

    public function value()
    {
        return $this->valor;
    }

    public function toArray()
    {
        return explode(",", $this->value());
    }

    public function __toString()
    {
        return empty($this->value()) ? "" : $this->value();
    }
}