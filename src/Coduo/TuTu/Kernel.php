<?php

namespace Coduo\TuTu;

use Coduo\TuTu\Extension\Initializer;
use Coduo\TuTu\Response\Builder;
use Coduo\TuTu\Response\Config\YamlLoader;
use Coduo\TuTu\Response\Config;
use Coduo\TuTu\Response\ConfigResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Yaml\Yaml;

class Kernel implements HttpKernelInterface
{
    /**
     * @var ServiceContainer
     */
    private $container;

    /**
     * @param ServiceContainer $container
     */
    public function __construct(ServiceContainer $container)
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

            $responseConfig = $this->container->getService('response.config.resolver')->resolveResponseConfig($request);
            if (isset($responseConfig)) {
                return $this->container->getService('response.builder')->build($responseConfig, $request);
            }
            return $this->container->getService('response.builder')->buildForMismatch($request);
        } catch (\Exception $e) {
            return $this->container->getService('response.builder')->buildForException($e);
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
        $this->container->setStaticDefinition('twig' ,function ($container) {
            $loader = new \Twig_Loader_String();
            $twig = new \Twig_Environment($loader, array(
                'cache' => $container->getParameter('tutu.root_path') . '/var',
            ));

            return $twig;
        });
    }

    private function registerExtensionInitializer()
    {
        $this->container->setDefinition('extension.initializer', function ($container) {
            return new Initializer();
        });
    }

    private function registerConfigLoader()
    {
        $this->container->setParameter(
            'response.config.yaml.path',
            $this->container->getParameter('tutu.root_path') . '/config/responses.yml'
        );

        $this->container->setDefinition('response.config.loader.yaml', function ($container) {
            return new YamlLoader($container->getParameter('response.config.yaml.path'));
        });
    }

    private function registerResponseConfigResolver()
    {
        $this->container->setStaticDefinition('response.config.resolver', function ($container) {
            return new ConfigResolver($container->getService('response.config.loader.yaml'));
        });
    }

    private function registerResponseBuilder()
    {
        $this->container->setDefinition('response.builder', function ($container) {
            return new Builder($container->getService('twig'));
        });
    }

    private function loadConfiguration()
    {
        $config = $this->parseConfiguration();
        if (array_key_exists('extensions', $config)) {
            foreach ($config['extensions'] as $extensionClass => $constructorArguments) {
                $extension = $this->container->getService('extension.initializer')->initialize($extensionClass, $constructorArguments);

                $extension->load($this->container);
            }
        }
    }

    private function parseConfiguration()
    {
        $configFiles = ['config.yml', 'config.yml.dist'];

        foreach ($configFiles as $fileName) {
            $filePath = sprintf('%s/config/%s', $this->container->getParameter('tutu.root_path'), $fileName);
            if ($filePath && file_exists($filePath) && $config = Yaml::parse(file_get_contents($filePath))) {
                return $config;
            }
        }

        return [];
    }
}
