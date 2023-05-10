<?php

namespace Modulo\Galeria\Entity;

use Core\Entity\Entity;
use Core\Entity\Value\EntityValueId;
use Core\Entity\Value\EntityValueLog;
use Core\Entity\Value\EntityValueSlug;
use Core\Entity\Value\EntityValueUuid;
use Core\Entity\Value\EntityValueArray;
use Core\Entity\Value\EntityValueBolean;
use Core\Entity\Value\EntityValueString;
use Core\Entity\Value\EntityValueDatetime;

class GaleriaEntity extends Entity
{
    const TABLE = 'galeria';

    /** @var EntityValueId */
    public $id;
    /** @var EntityValueUuid */
    public $uuid;
    /** @var EntityValueString */
    public $titulo;
    /** @var EntityValueSlug */
    public $slug;
    /** @var EntityValueString */
    public $descricao;
    /** @var EntityValueArray */
    public $tags;
    /** @var EntityValueDatetime */
    public $publicado;
    /** @var EntityValueDatetime */
    public $criado;
    /** @var EntityValueDatetime */
    public $alterado;
    /** @var EntityValueLog */
    public $log;
    /** @var EntityValueBolean */
    public $status;
    public $foto;
    public $fotos;

    public function noToArray()
    {
        return ['foto', 'fotos'];
    }


}