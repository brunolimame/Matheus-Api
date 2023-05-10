<?php


namespace Core\Entity\Value;

use Ramsey\Uuid\Uuid as RamseyUuid;

class EntityValueUuid implements EntityValueObjectInterface
{
    private $valor;

    public function __construct($valor = null)
    {
        $this->set($valor);
    }

    public static function factory($valor = null)
    {
        if(!is_null($valor)){
            return new self($valor);
        }
        return new self(RamseyUuid::uuid4()->toString());
    }

    public function set($valor)
    {
        $this->validarUuid($valor);
        $this->valor = $valor;
        return $this;
    }

    public function value():string
    {
        return $this->valor;
    }

    public function comparar(EntityValueUuid $outro):bool
    {
        return $this->value() === $outro->value();
    }

    private function validarUuid($uuid = null):void
    {
        if (!RamseyUuid::isValid($uuid) && !empty($uuid)) {
            throw new \InvalidArgumentException(sprintf('<%s>[%s] não permite o valor <%s>.', static::class, (new \ReflectionClass($this))->getShortName(), $uuid));
        }
    }

    public function __toString()
    {
        return empty($this->value()) ? "" : $this->value();
    }
}