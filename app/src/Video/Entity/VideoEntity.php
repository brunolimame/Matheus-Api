<?php

namespace Modulo\Video\Entity;

use Core\Lib\ParametrosArquivo;
use Core\Entity\Entity;
use Core\Entity\Value\EntityValueArray;
use Core\Entity\Value\EntityValueBolean;
use Core\Entity\Value\EntityValueDatetime;
use Core\Entity\Value\EntityValueEmbedVideo;
use Core\Entity\Value\EntityValueId;
use Core\Entity\Value\EntityValueJson;
use Core\Entity\Value\EntityValueLog;
use Core\Entity\Value\EntityValueSlug;
use Core\Entity\Value\EntityValueString;
use Core\Entity\Value\EntityValueThumbVideo;

class VideoEntity extends Entity
{

    const TABLE = 'video';

    const LOCAIS_SUPORTADOS = [
        'youtube'  => 'You Tube',
        'facebook' => 'Facebook',
        'vimeo'    => 'Vimeo',
    ];

    /** @var EntityValueId */
    public $id;
    /** @var EntityValueUuid */
    public $uuid;
    /** @var EntityValueString */
    public $titulo;
    /** @var EntityValueSlug */
    public $slug;
    public $thumb;
    public $embed;
    /** @var EntityValueString */
    public $descricao;
    /** @var EntityValueString */
    public $codigo;
    /** @var EntityValueString */
    public $local;
    /** @var EntityValueBolean */
    public $live;
    /** @var EntityValueArray */
    public $tags;
    /** @var EntityValueString */
    public $foto;
    /** @var EntityValueJson */
    public $foto_link;
    /** @var EntityValueDatetime */
    public $criado;
    /** @var EntityValueDatetime */
    public $alterado;
    /** @var EntityValueLog */
    public $log;
    /** @var EntityValueBolean */
    public $status;

    public function noToArray()
    {
        return ['thumb', 'embed'];
    }

    public function carregarDadosExtras():void
    {
        $this->thumb = EntityValueThumbVideo::factory($this->codigo->value(), $this->local->value(), $this->foto_link->toArray());
        $this->embed = EntityValueEmbedVideo::factory($this->codigo->value(), $this->local->value());
    }

    static public function getParametrosParaArquivo()
    {
        return ParametrosArquivo::load(
            "/assets/video",
            3,
            [
                [1280, 720, null],
                [720, 405, 'media'],
                [300, 169, 'thumb']
            ],
            ['image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/x-windows-bmp']
        );
    }


}