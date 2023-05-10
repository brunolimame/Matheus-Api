<?php

namespace Modulo\Academia\Provider;

use Modulo\Academia\Repository\AulaRepository;
use Modulo\Academia\Repository\CursoAulaRepository;
use Modulo\Academia\Repository\CursoAulaViewRepository;
use Modulo\Academia\Repository\CursoRepository;
use Modulo\Academia\Repository\FaqRepository;
use Modulo\Academia\Repository\ForumCategoriaRepository;
use Modulo\Academia\Repository\ForumTopicoRepository;
use Modulo\Academia\Repository\TrilhaRepository;
use Modulo\Academia\Repository\UsuarioAulaRepository;
use Slim\App;
use Core\Inferface\ProviderInterface;
use Modulo\Academia\Request\AcademiaRequestApi;

class AcademiaProvider implements ProviderInterface
{
  static public function load(App &$app, \stdClass $args = null)
  {
    $container = $app->getContainer();
    $conexaoBD = $container->get('db:conn');

    $container->set(AcademiaRequestApi::class, function () {
      return new AcademiaRequestApi();
    });

    $container->set(AulaRepository::class, function () use ($conexaoBD) {
      return new AulaRepository($conexaoBD);
    });
    $container->set(UsuarioAulaRepository::class, function () use ($conexaoBD) {
      return new UsuarioAulaRepository($conexaoBD);
    });
    $container->set(CursoRepository::class, function () use ($conexaoBD) {
      return new CursoRepository($conexaoBD);
    });
    $container->set(CursoAulaRepository::class, function () use ($conexaoBD) {
      return new CursoAulaRepository($conexaoBD);
    });
    $container->set(CursoAulaViewRepository::class, function () use ($conexaoBD) {
      return new CursoAulaViewRepository($conexaoBD);
    });
    $container->set(FaqRepository::class, function () use ($conexaoBD) {
      return new FaqRepository($conexaoBD);
    });
    $container->set(ForumCategoriaRepository::class, function () use ($conexaoBD) {
      return new ForumCategoriaRepository($conexaoBD);
    });
    $container->set(ForumTopicoRepository::class, function () use ($conexaoBD) {
      return new ForumTopicoRepository($conexaoBD);
    });
    $container->set(TrilhaRepository::class, function () use ($conexaoBD) {
      return new TrilhaRepository($conexaoBD);
    });
  }
}
