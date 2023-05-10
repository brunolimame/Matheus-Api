<?php

namespace Core\Entity;

use Core\Entity\Value\EntityValueBolean;
use PHPUnit\Framework\TestCase;


class EntityValueBoleanTest extends TestCase
{
    public function testFactory()
    {

        $entity = EntityValueBolean::factory();
        $this->assertInstanceOf(EntityValueBolean::class, $entity);
        $this->assertNull($entity->value());
        $this->assertIsString($entity->__toString());
        $this->assertNotEquals(2, $entity->__toString());
    }

    public function testFactoryComValor()
    {
        $entity = EntityValueBolean::factory(2);
        $this->assertInstanceOf(EntityValueBolean::class, $entity);
        $this->assertNotNull($entity->value());
        $this->assertIsBool($entity->value());
        $entity->set(0);
        $this->isFalse($entity->value());
        $entity->set(1);
        $this->isTrue($entity->value());
    }
}
