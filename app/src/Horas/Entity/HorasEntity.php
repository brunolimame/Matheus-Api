<?php

namespace Modulo\Horas\Entity;

use Core\Entity\Entity;
use Core\Entity\Value\EntityValueBolean;
use Core\Entity\Value\EntityValueString;


class HorasEntity extends Entity
{
    const TABLE = 'view_horas';

    /** @var EntityValueString */
    public $uuid;
    /** @var EntityValueString */
    public $designer_uuid;
    /** @var EntityValueString */
    public $cliente_uuid;
    /** @var EntityValueString */
    public $nome;
    /** @var EntityValueString */
    public $tema;
    /** @var EntityValueString */
    public $data;
    /** @var EntityValueString */
    public $razao_social;
    /** @var EntityValueString */
    public $qtd_posts;
    /** @var EntityValueBolean */
    public $planejamento;
}