<?php

namespace Core\Inferface;


interface ModuloConfigInterface
{
    static public function isEnable():bool;

    static public function getConf():array;
}