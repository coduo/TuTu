<?php

namespace Coduo\TuTu;

use Coduo\TuTu\Response\Builder;
use Coduo\TuTu\Response\Config\YamlLoader;
use Coduo\TuTu\Response\Config;
use Coduo\TuTu\Response\ConfigResolver;
use Pimple\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Kernel implements HttpKernelInterface
{
    /**
     * @var \Pimple\Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->setUpContainer();
    }

    /**
     * @param Request $request
     * @param int $type
     * @param bool $catch
     * @return Response
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        try {
            $responseConfig = $this->container['response.config.resolver']->resolveResponseConfig($request);
            if (isset($responseConfig)) {
                return $this->container['response.builder']->build($responseConfig, $request);
            }
            return $this->container['response.builder']->buildForMismatch($request);
        } catch (\Exception $e) {
            return $this->container['response.builder']->buildForException($e);
        }
    }

    private function setUpContainer()
    {
        $this->registerTwig();
        $this->registerConfigLoader();
        $this->registerResponseConfigResolver();
        $this->registerResponseBuilder();
    }

    private function registerTwig()
    {
        $this->container['twig'] = function ($container) {
            $loader = new \Twig_Loader_String();
            $twig = new \Twig_Environment($loader, array(
                'cache' => $container['tutu.root_path'] . '/var',
            ));

            return $twig;
        };
    }

    private function registerConfigLoader()
    {
        $this->container['response.config.yaml.path'] = $this->container['tutu.root_path'] . '/config/responses.yml';
        $this->container['response.config.loader.yaml'] = function ($container) {
            return new YamlLoader($container['response.config.yaml.path']);
        };
    }

    private function registerResponseConfigResolver()
    {
        $this->container['response.config.resolver'] = function ($container) {
            return new ConfigResolver($container['response.config.loader.yaml']);
        };
    }

    private function registerResponseBuilder()
    {
        $this->container['response.builder'] = function ($container) {
            return new Builder($container['twig']);
        };
    }
}
