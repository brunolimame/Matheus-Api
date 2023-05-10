<?php

namespace Core\Inferface;

use Slim\App;

interface ProviderInterface
{
    static public function load(App &$app, \stdClass $args = null);
}