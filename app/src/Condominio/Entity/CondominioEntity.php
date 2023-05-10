<?php

namespace Modulo\Condominio\Entity;

use Core\Entity\Entity;
use Core\Entity\Value\EntityValueId;
use Core\Entity\Value\EntityValueLog;
use Core\Entity\Value\EntityValueSlug;
use Core\Entity\Value\EntityValueUuid;
use Core\Entity\Value\EntityValueBolean;
use Core\Entity\Value\EntityValueString;
use Core\Entity\Value\EntityValueDatetime;

class CondominioEntity extends Entity
{
    const TABLE = 'condominio';

    /** @var EntityValueId */
    public $id;
    /** @var EntityValueUuid */
    public $uuid;
    /** @var EntityValueString */
    public $nome;
    /** @var EntityValueSlug */
    public $slug;
    /** @var EntityValueSlug */
    public $endereco;
    /** @var EntityValueSlug */
    public $cidade;
    /** @var EntityValueSlug */
    public $uf;
    /** @var EntityValueSlug */
    public $cep;
    /** @var EntityValueDatetime */
    public $criado;
    /** @var EntityValueDatetime */
    public $alterado;
    /** @var EntityValueLog */
    public $log;
    /** @var EntityValueBolean */
    public $status;
}