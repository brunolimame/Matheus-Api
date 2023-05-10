<?php

namespace Core\Entity;

use Core\Entity\Value\EntityValueDecimal;
use PHPUnit\Framework\TestCase;


class EntityValueDecimalTest extends TestCase
{
    public function testFactory()
    {
        $entity = EntityValueDecimal::factory();
        $this->assertInstanceOf(EntityValueDecimal::class, $entity);
        $this->assertNotNull($entity->value());
        $this->assertEquals(0, $entity->value());
        $this->assertIsString($entity->__toString());
        $this->assertEquals('', $entity->__toString());
    }

    public function testTratarValores()
    {
        $valores = ['1.010,25', '1010,25', '1010.25', '1,010.25', 'R$ 1,010.25', '$1,010.25'];

        $entity = EntityValueDecimal::factory();
        array_map(function ($valor) use (&$entity) {
            $entity->set($valor);
            $this->assertEquals(1010.25, $entity->value());
        }, $valores);

        $entity->set("abc");
        $this->assertEquals(0.0, $entity->value());
    }
}
