<?php


namespace Modulo\Galeria\Entity;


use Core\Entity\Entity;
use Core\Lib\ParametrosArquivo;
use Core\Entity\Value\EntityValueId;
use Core\Entity\Value\EntityValueJson;
use Core\Entity\Value\EntityValueUuid;
use Core\Entity\Value\EntityValueBolean;
use Core\Entity\Value\EntityValueString;
use Core\Entity\Value\EntityValueInteiro;
use Core\Entity\Value\EntityValueDatetime;

class GaleriaFotoEntity extends Entity
{
    const TABLE = 'galeria_foto';

    /** @var EntityValueId */
    public $id;
    /** @var EntityValueUuid */
    public $uuid;
    /** @var EntityValueInteiro */
    public $galeria_id;
    /** @var EntityValueString */
    public $galeria_uuid;
    public static $pastaFoto;
    /** @var EntityValueString */
    public $foto;
    /** @var EntityValueJson */
    public $foto_link;
    /** @var EntityValueString */
    public $legenda;
    /** @var EntityValueInteiro */
    public $ordem;
    /** @var EntityValueDatetime */
    public $criado;
    /** @var EntityValueDatetime */
    public $alterado;
    /** @var EntityValueBolean */
    public $status;

    public function carregarDadosExtras():void
    {
        self::$pastaFoto = $this->galeria_uuid->value();
    }

    public function noToArray()
    {
        return ['pastaFoto'];
    }

    static public function getParametrosParaArquivo()
    {
        return ParametrosArquivo::load(
            sprintf("/assets/galeria/%s", self::$pastaFoto),
            10,
            [
                [1280, 720, null],
                [720, 405, 'media'],
                [300, 169, 'thumb']
            ],
            ['image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/x-windows-bmp']
        );
    }

}