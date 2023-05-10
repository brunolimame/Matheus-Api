<?php


namespace Core\Entity;

use Core\Entity\Value\EntityValueBolean;
use Core\Entity\Value\EntityValueDate;
use Core\Entity\Value\EntityValueDatetime;
use Core\Entity\Value\EntityValueJson;
use Core\Entity\Value\EntityValueLog;
use Core\Entity\Value\EntityValueObjectInterface;
use Core\Inferface\EntityInterface;

class Entity implements EntityInterface
{
    public function __construct($data = [])
    {
        if (!empty($data)) {
            $this->hydrator($data);
        }
        $this->convertToPHPValue();
    }

    /**
     * @param array $data
     * @return $this
     */
    public function hydrator($data = [])
    {
        foreach ($data as $name => $value) {
            if (property_exists($this, $name)) {
                if ($this->$name instanceof EntityValueObjectInterface) {
                    $this->$name->set($value);
                } else {
                    $this->$name = $value;
                }
            }
        }
        return $this;
    }

    private function carregarAnotacao()
    {
        $reflector       = new \ReflectionClass(get_class($this));
        $selfEntity      = &$this;
        $variaveisEntity = array_keys(get_object_vars($this));
        array_walk($variaveisEntity, function ($propety) use ($reflector, &$selfEntity) {
            if (!$selfEntity->$propety instanceof EntityValueObjectInterface) {
                $readPropety = $reflector->getProperty($propety)
                    ->getDocComment();
                $pattern     = "#(@[a-zA-Z]+\s*[a-zA-Z0-9\=\&, ()_].*)#";
                preg_match_all($pattern, $readPropety, $matches, PREG_PATTERN_ORDER);

                $patternDoc = "#@([a-zA-Z]+)\s([a-zA-Z0-9\=\&, ()_].*)#";
                preg_match_all($patternDoc, trim(str_replace(["*", "/"], '', current($matches[1]))), $docMatches, PREG_PATTERN_ORDER);

                $classObjectValue = "Core\\Entity\\Value\\" . trim(current($docMatches[2]));
                if (class_exists($classObjectValue)) {
                    $selfEntity->$propety = $classObjectValue::factory($selfEntity->$propety);
                }
            }
        }, []);
        return $this;
    }

    private function convertToPHPValue()
    {
        $this
            ->carregarAnotacao()
            ->carregarDadosExtras();
    }

    public function carregarDadosExtras():void
    {
    }

    /**
     * @return array
     */
    public function noToArray()
    {
        return [];
    }

    /**
     * @return array
     */
    public function toArray():array
    {
        $noToArray = array_merge($this->noToArray(), ['criado', 'alterado', '_container']);
        return array_reduce(array_keys(get_object_vars($this)), function ($result, $item) use ($noToArray) {
            if (!in_array($item, $noToArray)) {
                $result[$item] = $this->$item instanceof EntityValueObjectInterface ? $this->$item->value() : $this->$item;
            }
            return $result;
        }, []);
    }

    public function toApi()
    {
        $this->carregarAnotacao();
        return array_reduce(array_keys(get_object_vars($this)), function ($result, $item) {
            if (!in_array($item, ['_container'])) {
                if ($this->$item instanceof EntityValueObjectInterface) {
                    if ($this->$item instanceof EntityValueDatetime) {
                        $objeto        = $this->$item->object();
                        $result[$item] = (object)[
                            'object' => $objeto,
                            'value'  => $this->$item->value(),
                            'data'   => $objeto instanceof \DateTime ? $objeto->format("d/m/Y") : null,
                            'data2'  => $objeto instanceof \DateTime ? $objeto->format("d-m-Y") : null,
                            'data3'  => $objeto instanceof \DateTime ? $objeto->format("d.m.Y") : null,
                            'data4'  => $objeto instanceof \DateTime ? $this->$item->extenso() : null,
                            'data5'  => $objeto instanceof \DateTime ? $this->$item->extenso(true) : null,
                            'hora'   => $objeto instanceof \DateTime ? $objeto->format("H:i") : null,
                            'horas'  => $objeto instanceof \DateTime ? $objeto->format("H:i:s") : null,
                        ];
                    } else if ($this->$item instanceof EntityValueDate) {
                        $objeto        = $this->$item->object();
                        $result[$item] = (object)[
                            'object' => $this->$item->object(),
                            'value'  => $this->$item->value(),
                            'data'   => $objeto instanceof \DateTime ? $objeto->format("d/m/Y") : null,
                            'data2'  => $objeto instanceof \DateTime ? $objeto->format("d-m-Y") : null,
                            'data3'  => $objeto instanceof \DateTime ? $objeto->format("d.m.Y") : null,
                            'data4'  => $objeto instanceof \DateTime ? $this->$item->extenso() : null,
                            'data5'  => $objeto instanceof \DateTime ? $this->$item->extenso(true) : null,
                        ];
                    } else if ($this->$item instanceof EntityValueLog || $this->$item instanceof EntityValueJson) {
                        $dados         = $this->$item->toArray();
                        $result[$item] = !empty($dados) ? (object)$dados : $dados;
                    } else {
                        $result[$item] = $this->$item->value();
                    }
                } else {
                    if (method_exists($this->$item, 'toArray')) {
                        $result[$item] = $this->$item->toArray();
                    } else {
                        $result[$item] = $this->$item;
                    }
                }
            }
            return $result;
        }, []);
    }

    public function toSave($evento = null,$usuario=null)
    {
        if (property_exists($this, 'log')) {
            $eventName = is_null($evento) ? 'novo' : $evento;
            if ($this->id->value() > 0) {
                $eventName = is_null($evento) ? 'editar' : $evento;
            }
            $this->log->add($usuario, $eventName);
        }

        if (property_exists($this, 'slug')) {
            if (property_exists($this, 'titulo')) {
                $this->slug->add($this->titulo->value());
            }
            if (property_exists($this, 'nome')) {
                $this->slug->add($this->nome->value());
            }
        }
        $variaveisEntity = array_keys(get_object_vars($this));
        $selfEntity      = &$this;
        array_walk($variaveisEntity, function ($propety) use (&$selfEntity) {
            if ($selfEntity->$propety instanceof EntityValueObjectInterface) {
                $selfEntity->$propety = $selfEntity->$propety instanceof EntityValueBolean ? $selfEntity->$propety->raw() : $selfEntity->$propety->value();
            }
        }, []);
        return $this->toArray();
    }


    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        // TODO: Implement __set() method.
    }

}