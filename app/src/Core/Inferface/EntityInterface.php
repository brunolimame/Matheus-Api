<?php

namespace Core\Inferface;

interface EntityInterface
{
    public function hydrator($data = []);

    public function carregarDadosExtras();

    public function noToArray();

    public function toArray();
    
    public function toApi();

    public function toSave($evento = null);
}