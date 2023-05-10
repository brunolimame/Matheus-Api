<?php

namespace Core\Entity;

use Core\Entity\Value\EntityValueDatetime;
use PHPUnit\Framework\TestCase;


class EntityValueDatetimeTest extends TestCase
{
    public function testFactory()
    {

        $entity = EntityValueDatetime::factory();
        $this->assertInstanceOf(EntityValueDatetime::class, $entity);
        $this->assertNull($entity->value());
        $this->assertIsString($entity->__toString());
    }

    protected function iniciandoInstancia(): EntityValueDatetime
    {
        return EntityValueDatetime::factory('2022-03-23 00:00:00');
    }

    public function testCriacaoDoObjeto()
    {
        $entity = $this->iniciandoInstancia();
        $this->assertInstanceOf(\Datetime::class, $entity->object());
    }

    public function testAtribuirValorNoFormatoSql()
    {
        $entity = EntityValueDatetime::factory();
        $entity->set('2022-03-23 00:00:00');
        $this->assertInstanceOf(\Datetime::class, $entity->object());
    }

    public function testAtribuirValorNoFormatoBr()
    {
        $entity = EntityValueDatetime::factory();
        $entity->setConvert('23/03/2022 00:00:00', $entity::DATETIME_FORMAT_BR);
        $this->assertInstanceOf(\Datetime::class, $entity->object());
    }

    public function testConvertendoDataNoFormatoBrasileiro()
    {
        $entity = $this->iniciandoInstancia();
        preg_match("%([0-9]+)/([0-9]+)/([0-9]+)\s([0-9]+):([0-9]+)%", $entity->formatBr(), $match);
        $this->assertNotEmpty($match);
    }

    public function testDataPorExtenso()
    {
        $entity = $this->iniciandoInstancia();
        $this->assertEquals('23 de Março de 2022', $entity->extenso());
    }
}
