<?php


namespace Core\Entity\Value;


class EntityValueString implements EntityValueObjectInterface
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
        $this->validarString($valor);;
        $this->valor = $valor;
        return $this;
    }

    public function value()
    {
        return $this->valor;
    }

    public function validarString($valor = null):void
    {
        if (!is_string($valor) && !empty($valor)) {
            throw new \InvalidArgumentException(sprintf('<%s>[%s] não permite o valor <%s>.', static::class, (new \ReflectionClass($this))->getShortName(),$valor));
        }
    }

    public function __toString()
    {
        return empty($this->value()) ? "" : $this->value();
    }
}