<?php

namespace Core\Entity;

use Core\Entity\Value\EntityValueId;
use PHPUnit\Framework\TestCase;


class EntityValueIDTest extends TestCase
{
    public function testFactory()
    {

        $entity = EntityValueId::factory();
        $this->assertInstanceOf(EntityValueId::class, $entity);
        $this->assertNull($entity->value());
        $this->assertIsString($entity->__toString());
        $this->assertNotEquals(2, $entity->__toString());
    }

    public function testFactoryComValor()
    {

        $entity = EntityValueId::factory(2);
        $this->assertInstanceOf(EntityValueId::class, $entity);
        $this->assertNotNull($entity->value());
        $this->assertIsInt($entity->value());
        $this->assertEquals(2, $entity->value());
        $this->assertEquals(2, $entity->__toString());
    }

    public function testFactoryComValorEmString()
    {

        $entity = EntityValueId::factory('2');
        $this->assertInstanceOf(EntityValueId::class, $entity);
        $this->assertNotNull($entity->value());
        $this->assertIsInt($entity->value());
        $this->assertEquals(2, $entity->value());
        $this->assertEquals(2, $entity->__toString());
    }
}
