<?php


namespace Core\Entity\Value;


class EntityValueBolean implements EntityValueObjectInterface
{
    private $valor;

    public function __construct($valor = null)
    {
        $this->set($valor);
    }

    public static function factory($valor = null)
    {
        if (!is_null($valor)) {
            return new self((int) $valor);
        }
        return new self($valor);
    }

    public function set($valor = null)
    {
        $this->valor = !is_null($valor) ? (bool)$valor : null;
        return $this;
    }

    public function value()
    {
        return $this->valor;
    }

    public function raw()
    {
        return is_null($this->value()) ? null : (int)$this->value();
    }

    public function __toString()
    {
        return empty($this->value()) ? "" : $this->value();
    }
}
