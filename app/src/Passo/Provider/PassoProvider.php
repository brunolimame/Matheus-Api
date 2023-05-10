<?php

namespace Modulo\Passo\Provider;

use Modulo\Passo\Repository\PassoArquivoRepository;
use Slim\App;
use Boot\Provider\Jwt\JwtParse;
use Laminas\Permissions\Acl\Acl;
use Core\Inferface\ProviderInterface;
use Modulo\Passo\Provider\PassoAclProvider;
use Modulo\Passo\Repository\PassoRepository;
use Modulo\Passo\Request\PassoRequestApi;

class PassoProvider implements ProviderInterface
{
    static public function load(App &$app, \stdClass $args = null)
    {
        $container = $app->getContainer();
        $conexaoBD = $container->get('db:conn');
        $container->set(PassoAclProvider::class, function (Acl $acl, JwtParse $jwtParse) {
            return new PassoAclProvider($acl, $jwtParse);
        });
        $container->set(PassoRequestApi::class, function () {
            return new PassoRequestApi();
        });

        $container->set(PassoRepository::class, function () use ($conexaoBD) {
            return new PassoRepository($conexaoBD);
        });
        $container->set(PassoArquivoRepository::class, function () use ($conexaoBD) {
            return new PassoArquivoRepository($conexaoBD);
        });
    }
}
