<?php


namespace Core\Entity\Value;

use Core\Lib\Slug;

class EntityValueSlug implements EntityValueObjectInterface
{
    private $valor;

    public function __construct($valor = null)
    {
        $this->set($valor);
    }

    public static function factory($valor = null, $novo = null)
    {
        $novo = new self($valor);
        if (!empty($novo)) {
            $novo->add($novo);
        }
        return $novo;
    }

    public function set($valor)
    {
        $this->valor = $valor;
        return $this;
    }

    public function value()
    {
        return $this->valor;
    }

    public function add($valor)
    {
        $this->set(Slug::slug($valor));
        return $this;
    }

    public function __toString()
    {
        return empty($this->value()) ? "" : $this->value();
    }
}