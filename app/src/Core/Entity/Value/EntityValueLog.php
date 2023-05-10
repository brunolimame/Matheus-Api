<?php


namespace Core\Entity\Value;

use Core\Lib\IPTrait;

class EntityValueLog implements EntityValueObjectInterface
{
    private $valor;
    use IPTrait;

    public function __construct($valor = null)
    {
        $this->set($valor);
        $this->validarSerialize($this->value());
    }

    public static function factory($valor = null)
    {
        return new self($valor);
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

    /**
     * @param array $usuario
     * @param $evento
     */
    public function add($usuario = [], $evento = null)
    {
        $novoLog[] = [
            'user'  => $usuario,
            'event' => $evento,
            'ip'    => $this->getIpCliente(),
            'data'  => (new \DateTime('now'))->format('Y-m-d H:i:s')
        ];

        if (!empty($this->value())) {
            $oldLogArray = $this->toArray();
            if (is_array($oldLogArray)) {
                foreach ($oldLogArray as $l) {
                    $novoLog[] = $l;
                }
            }
        }

        $this->set(serialize($novoLog));
    }

    public function toArray()
    {
        if (empty($this->value())) {
            return null;
        }
        $this->validarSerialize($this->value());
        return unserialize($this->value());
    }

    public function validarSerialize($valor = null)
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