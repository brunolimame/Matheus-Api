<?php


namespace Core\Entity\Value;

class EntityValueSerialize implements EntityValueObjectInterface
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
        $this->validarSerialize($valor);
        return $this;
    }

    public function value():string
    {
        return $this->valor;
    }

    public function toArray():array
    {
        $this->validarSerialize($this->value());
        return unserialize($this->value());
    }

    public function validarSerialize($valor = null):void
    {
        if (!@unserialize($valor) && !empty($valor)) {
            throw new \InvalidArgumentException(sprintf('<%s>[%s] não permite o valor <%s>.', static::class, (new \ReflectionClass($this))->getShortName(), $valor));
        }
    }

    public function __toString()
    {
        return empty($this->value()) ? "" : $this->value();
    }
}