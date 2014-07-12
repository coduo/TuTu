<?php

namespace Coduo\TuTu;

use Coduo\TuTu\Extension\Initializer;
use Coduo\TuTu\Response\Builder;
use Coduo\TuTu\Response\Config\YamlLoader;
use Coduo\TuTu\Response\Config;
use Coduo\TuTu\Response\ConfigResolver;
use Pimple\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Yaml\Yaml;

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
            $this->loadConfiguration();

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
        $this->registerExtensionInitializer();
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

    private function registerExtensionInitializer()
    {
        $this->container['extension.initializer'] = function ($container) {
            return new Initializer();
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

    private function loadConfiguration()
    {
        $config = $this->parseConfiguration();
        if (array_key_exists('extensions', $config)) {
            foreach ($config['extensions'] as $extensionClass => $constructorArguments) {
                $extension = $this->container['extension.initializer']->initialize($extensionClass, $constructorArguments);

                $extension->load($this->container);
            }
        }
    }

    private function parseConfiguration()
    {
        $configFiles = ['config.yml', 'config.yml.dist'];

        foreach ($configFiles as $fileName) {
            $filePath = sprintf('%s/config/%s', $this->container['tutu.root_path'], $fileName);
            if ($filePath && file_exists($filePath) && $config = Yaml::parse(file_get_contents($filePath))) {
                return $config;
            }
        }

        return [];
    }
}
