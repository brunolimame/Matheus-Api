<?php

namespace Core\Entity;

use Core\Entity\Value\EntityValueJson;
use PHPUnit\Framework\TestCase;


class EntityValueJsonTest extends TestCase
{
    public function testFactory()
    {
        $entity = EntityValueJson::factory();
        $this->assertInstanceOf(EntityValueJson::class, $entity);
        $this->assertNull($entity->value());
        $this->assertIsString($entity->__toString());
        $this->assertNotEquals(2, $entity->__toString());
    }

    public function testFactoryComValor()
    {
        $json = '{
            "_id": "6241e1b0b52cf9cd1527f20c",
            "index": 0,
            "guid": "7335451d-35da-4bec-91d7-cc5571493863",
            "isActive": false,
            "balance": "$3,450.41",
            "picture": "http://placehold.it/32x32"
          }';
        $entity = EntityValueJson::factory($json);
        $this->assertInstanceOf(EntityValueJson::class, $entity);
        $this->assertNotNull($entity->value());
        $this->isJson($entity->value());
        $this->assertJsonStringEqualsJsonString($json, $entity->value());
        $this->assertIsArray($entity->toArray());
        $this->assertEquals("6241e1b0b52cf9cd1527f20c", $entity->toArray()['_id']);
        $this->assertIsInt($entity->toArray()['index']);
        $this->assertIsBool($entity->toArray()['isActive']);
    }
}
