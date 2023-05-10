<?php

namespace Boot;

use Slim\App;
use Nyholm\Psr7\Response;
use Core\Request\RequestApi;
use Boot\Provider\JwtProvider;
use Slim\Routing\RouteContext;
use Laminas\Permissions\Acl\Acl;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\Finder;
use Core\Inferface\ProviderInterface;
use Slim\Routing\RouteCollectorProxy;
use Core\Inferface\ModuloConfigInterface;
use Nyholm\Psr7\ServerRequest as Request;
use Symfony\Component\Finder\SplFileInfo;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Permissions\Acl\Role\GenericRole as Role;
use Laminas\Permissions\Acl\Resource\GenericResource as Resource;

class BootLoad
{
    protected $app;
    protected const ARQUIVO_CONFIG_APP = __DIR__ . '/../storage/app.yaml';
    protected const ARQUIVO_CACHE_MODULOS = __DIR__ . '/../storage/ModuloCache.yaml';

    public function __construct(App &$app)
    {
        $this->app = $app;
    }

    public function loadCors()
    {
        $this->app->addBodyParsingMiddleware();

//        $this->app->add(function (Request $request, RequestHandlerInterface $handler): Response {
//
//            $gerarNovoToken = function () {
//                $api = RequestApi::factory('POST');
//                $resultApi = $api
//                    ->setEndpoint('/auth')
//                    ->exec();
//                $_SESSION[JwtProvider::JWT_HEADER] = $resultApi->token;
//            };
//
//            if (empty($_SESSION[JwtProvider::JWT_HEADER])) {
//                $requestPath = $request->getUri()->getPath();
//                preg_match('/\/json?(.*)/', $requestPath, $requestJson);
//                preg_match('/\/auth?(.*)/', $requestPath, $requestAuth);
//
//                if (empty($requestJson) && empty($requestAuth)) {
//                    $gerarNovoToken();
//                }
//            } else {
//                $api = RequestApi::factory('POST');
//                $resultApi = $api
//                    ->setEndpoint('/auth/validar')
//                    ->exec();
//                if (!$resultApi->token) {
//                    $gerarNovoToken();
//                }
//            }
//
//            return $handler->handle($request);
//        });

        $this->app->add(function (Request $request, RequestHandlerInterface $handler): Response {
            $routeContext   = RouteContext::fromRequest($request);
            $routingResults = $routeContext->getRoutingResults();
            $methods        = $routingResults->getAllowedMethods();
            $response     = $handler->handle($request);

            $response     = $response->withHeader('Access-Control-Allow-Origin', "*");
            $response     = $response->withHeader('Access-Control-Allow-Methods', implode(", ",$methods));
            $response     = $response->withHeader('Access-Control-Allow-Headers', "X-Requested-With, Content-Type, Accept, Origin, Authorization, ".JwtProvider::JWT_HEADER.", ".JwtProvider::JWT_HEADER_REFRESH);

            // Optional: Allow Ajax CORS requests with Authorization header
            $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');

            $appDebug = $this->get('debug');
            if ($appDebug) {
                $response = $response->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
                $response = $response->withHeader('Pragma', 'no-cache');
                $response = $response->withHeader('Expires', '0');
            } elseif (!$response->hasHeader('Cache-Control')) {
                $response = $response->withHeader('Cache-Control', 'public, max-age=60, min-fresh=60, must-revalidate');
                $response = $response->withHeader('Pragma', 'no-cache');
                $response = $response->withHeader('Expires', '60');
            }

            return $response;
        });
        $this->app->addRoutingMiddleware();

        return $this;
    }

    static public function loadConfigApp()
    {
        if (file_exists(self::ARQUIVO_CONFIG_APP)) {
            $lerArquivo = Yaml::parseFile(self::ARQUIVO_CONFIG_APP);
        } else {
            $lerArquivo = [
                'debug' => true
            ];
            $gerarYaml  = Yaml::dump($lerArquivo);
            file_put_contents(self::ARQUIVO_CONFIG_APP, $gerarYaml);
        }
        return $lerArquivo;
    }

    public function loadProviders($listProviders = [])
    {
        if (is_array($listProviders) && !empty($listProviders)) {
            array_walk($listProviders, function ($args, $provider) {
                if ((new $provider()) instanceof ProviderInterface) {
                    if (!$args instanceof \stdClass) {
                        if (empty($args)) {
                            $args = new \stdClass();
                        } else if (is_array($args)) {
                            $args = (object)$args;
                        }
                    }
                    /** @var ProviderInterface $provider */
                    $provider::load($this->app, $args);
                }
            });
        }
        return $this;
    }
    

    public function loadModulos()
    {
        $appDebug = $this->app->getContainer()->get('debug');
        if (!file_exists(self::ARQUIVO_CACHE_MODULOS)) {
            $gerarYaml = Yaml::dump($this->factoryCacheModulos($appDebug), 2, 4, Yaml::DUMP_OBJECT);
            file_put_contents(self::ARQUIVO_CACHE_MODULOS, $gerarYaml);
        }

        $cacheDosModulos = Yaml::parseFile(self::ARQUIVO_CACHE_MODULOS, Yaml::PARSE_OBJECT);

        if ($appDebug || $cacheDosModulos->debug != $appDebug) {
            $buscarConfigDosModulos = (new Finder())
                ->files()
                ->in(__DIR__ . '/./../src')
                ->exclude('Core')
                ->name('ModuloConfig.php');


            if (!$buscarConfigDosModulos->hasResults()) {
                throw new \Exception("Nenhum módulo encontrado");
            }

            $listaModulosAtivos = array_reduce(iterator_to_array($buscarConfigDosModulos), function ($result, SplFileInfo $modulo) {
                $namespace = sprintf("\\Modulo\\%s\\%s", $modulo->getRelativePath(), $modulo->getFilenameWithoutExtension());
                if ((new $namespace()) instanceof ModuloConfigInterface) {
                    /** @var ModuloConfigInterface $namespace */
                    if ($namespace::isEnable()) {
                        $result = array_merge_recursive($result, $namespace::getConf());
                    }
                }
                return $result;
            }, []);

            $dadosParaCache     = $this->factoryCacheModulos($appDebug, $listaModulosAtivos);
            $gerarYamlNovoCache = Yaml::dump($dadosParaCache, 2, 4, Yaml::DUMP_OBJECT);
            file_put_contents(self::ARQUIVO_CACHE_MODULOS, $gerarYamlNovoCache);
            $listaModulos = $dadosParaCache;
        } else {
            $listaModulos = $cacheDosModulos;
        }

        if (!empty($listaModulos->modulo['acl'])) {
            $this->loadAcl($listaModulos->modulo['acl']);
        }

        $this
            ->loadProviders($listaModulos->modulo['provider'])
            ->loadRouters($listaModulos->modulo['router']);

        return $this;
    }


    public function loadAcl($listaModulos)
    {

        $acl = new Acl();

        $acl->addRole(new Role('convidado'))
            ->addRole(new Role('usuario'))
            ->addRole(new Role('moderador'), 'usuario')
            ->addRole(new Role('admin'))
            ->addRole(new Role('sadmin'));

        $recursos = array_keys($listaModulos);

        array_walk($recursos, function ($recurso) use (&$acl) {
            $acl->addResource(new Resource($recurso));
        });

        array_walk($listaModulos, function ($funcoes, $recurso) use (&$acl) {
            array_walk($funcoes, function ($funcao, $nivel) use ($recurso, &$acl) {
                $acl->allow($nivel, $recurso, $funcao);
            });
        });

        $acl->allow('admin');
        $acl->deny('admin', 'usuario', 'sadmin');
        $acl->allow('sadmin');
        $container = $this->app->getContainer();
        $container->set(Acl::class, $acl);
    }

    public function loadRouters($listRouters = [])
    {
        $normalizeNameRouter = function ($routerName, $isFistRouterLevel = false) {
            return empty($routerName) ? ($isFistRouterLevel) ? "/" : "" : '/' . $routerName;
        };
        if (is_array($listRouters) && !empty($listRouters)) {
            array_walk($listRouters, function ($routerClass, $routerName) {
                $this->factoryRouter($routerClass, $routerName);
            });
        }
    }

    static protected function normalizeNameRouter($routerName, $isFistRouterLevel = false): string
    {
        return empty($routerName) ? ($isFistRouterLevel) ? "/" : "" : '/' . $routerName;
    }

    /**
     * @param $routerClass
     * @param $routerName
     */
    protected function factoryRouter($routerClass, $routerName): void
    {
        if (!is_array($routerClass)) {
            $this->app->group(self::normalizeNameRouter($routerName, true), $routerClass);
        } else {
            $this->app->group(self::normalizeNameRouter($routerName), function (RouteCollectorProxy $group) use (&$routerClass) {
                self::factoryRouterGroup($group, $routerClass);
            });
        }
    }

    static protected function factoryRouterGroup(&$group, &$routes): void
    {

        array_walk($routes, function (&$routerClass, $routerName) use (&$group) {
            if (!is_array($routerClass)) {
                $group->group(self::normalizeNameRouter($routerName), $routerClass);
            } else {
                $group->group(self::normalizeNameRouter($routerName), function (RouteCollectorProxy $recursiveGroup) use (&$routerClass) {
                    self::factoryRouterGroup($recursiveGroup, $routerClass);
                });
            }
        });
    }

    /**
     * @param bool $debug
     * @param array $modulos
     * @return object
     */
    protected function factoryCacheModulos($debug = true, $modulos = [])
    {
        return (object)[
            'debug'  => $debug,
            'modulo' => $modulos
        ];
    }
}
