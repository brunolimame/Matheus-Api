<?php

namespace Core\Entity;

use Core\Entity\Value\EntityValueArray;
use PHPUnit\Framework\TestCase;


class EntityValueArrayTest extends TestCase
{
    public function testFactory()
    {
        $entity = EntityValueArray::factory();
        $this->assertInstanceOf(EntityValueArray::class, $entity);
        $this->assertNull($entity->value());
        $this->assertIsString($entity->__toString());
        $this->assertNotEquals(2, $entity->__toString());
    }

    public function testFactoryComValor()
    {
        $entity = EntityValueArray::factory('a,b,c,d,1,2');
        $this->assertInstanceOf(EntityValueArray::class, $entity);
        $this->assertNotNull($entity->value());
        $this->assertIsArray($entity->toArray());
        $entity->set(null);
        $this->isNull($entity->value());
    }
}
