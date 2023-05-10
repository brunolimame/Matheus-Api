<?php


namespace Core\Entity\Value;

use Respect\Validation\Validator;

class EntityValueJson implements EntityValueObjectInterface
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
            $this->valor = @json_encode($valor);
        }
        $this->validarJson($this->value());
        return $this;
    }

    public function value()
    {
        return $this->valor;
    }

    public function toArray($key = null)
    {
        $this->validarJson($this->value());
        $decode = json_decode($this->value(), true);
        if (empty($decode)) {
            return null;
        }
        if (!empty($key)) {
            return $decode[$key];
        }
        return !empty($key) ? $decode[$key] : $decode;
    }

    private function validarJson(string $valor = null):void
    {

        if (!Validator::json()->validate($valor) && !empty($valor)) {
            throw new \InvalidArgumentException(sprintf('<%s>[%s] não permite o valor <%s>.', static::class, (new \ReflectionClass($this))->getShortName(), $valor));
        }
    }

    public function __toString()
    {
        return empty($this->value()) ? "" : $this->value();
    }
}