<?php

namespace Core\Entity;

use Core\Entity\Value\EntityValueDate;
use PHPUnit\Framework\TestCase;


class EntityValueDateTest extends TestCase
{
    public function testFactory()
    {

        $entity = EntityValueDate::factory();
        $this->assertInstanceOf(EntityValueDate::class, $entity);
        $this->assertNull($entity->value());
        $this->assertIsString($entity->__toString());
    }

    protected function iniciandoInstancia(): EntityValueDate
    {
        return EntityValueDate::factory('2022-03-23');
    }

    public function testCriacaoDoObjeto()
    {
        $entity = $this->iniciandoInstancia();
        $this->assertInstanceOf(\Datetime::class, $entity->object());
    }

    public function testAtribuirValorNoFormatoSql()
    {
        $entity = EntityValueDate::factory();
        $entity->set('2022-03-23');
        $this->assertInstanceOf(\Datetime::class, $entity->object());
    }

    public function testAtribuirValorNoFormatoBr()
    {
        $entity = EntityValueDate::factory();
        $entity->setDateConvert('23/03/2022', $entity::DATE_FORMAT_BR);
        $this->assertInstanceOf(\Datetime::class, $entity->object());
    }

    public function testConvertendoDataNoFormatoBrasileiro()
    {
        $entity = $this->iniciandoInstancia();
        preg_match("%([0-9]+)/([0-9]+)/([0-9]+)%", $entity->formatDateBr(), $match);
        $this->assertNotEmpty($match);
    }

    public function testDataPorExtenso()
    {
        $entity = $this->iniciandoInstancia();
        $this->assertEquals('23 de Março de 2022', $entity->extenso());
    }
}
