<?php

namespace Coduo\TuTu;

use Coduo\TuTu\Config\Loader\YamlLoader;
use Coduo\TuTu\Config\Resolver;
use Coduo\TuTu\Extension\Initializer;
use Coduo\TuTu\Request\ChainMatchingPolicy;
use Coduo\TuTu\Request\MethodMatchingPolicy;
use Coduo\TuTu\Request\RouteMatchingPolicy;
use Coduo\TuTu\Response\Builder;
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
            $configElement = $this->container->getService('response.config.resolver')->resolveConfigElement($request);
            if (isset($configElement)) {
                return $this->container->getService('response.builder')->build($configElement, $request);
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
        $this->registerRequestMatchingPolicy();
        $this->registerConfigResolver();
        $this->registerResponseBuilder();
    }

    private function registerTwig()
    {
        $resourcesPath = $this->container->getParameter('tutu.root_path') . '/resources';
        if ($customPath = getenv('tutu_resources')) {
            if (!file_exists($customPath)) {
                throw new \RuntimeException('Custom resources path not found at '.$customPath);
            }

            $resourcesPath = $customPath;
        }

        $this->container->setStaticDefinition('twig' ,function ($container) use ($resourcesPath) {
            $stringLoader = new \Twig_Loader_String();
            $filesystemLoader = new \Twig_Loader_Filesystem();
            $filesystemLoader->addPath($resourcesPath, 'resources');

            $loader = new \Twig_Loader_Chain([$filesystemLoader, $stringLoader]);
            $twig = new \Twig_Environment($loader, [
                'cache' => $container->getParameter('tutu.root_path') . '/var/twig',
            ]);

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
        $responsesPath = $this->container->getParameter('tutu.root_path') . '/config/responses.yml';
        if ($customPath = getenv('tutu_responses')) {
            if (!file_exists($customPath)) {
                throw new \RuntimeException('Custom responses file not found at '.$customPath);
            }

            $responsesPath = $customPath;
        }

        $this->container->setParameter(
            'response.config.yaml.path',
            $responsesPath
        );

        $this->container->setDefinition('response.config.loader.yaml', function ($container) {
            return new YamlLoader($container->getParameter('response.config.yaml.path'));
        });
    }

    private function registerRequestMatchingPolicy()
    {
        $this->container->setDefinition('request.matching_policy.method', function($container) {
            return new MethodMatchingPolicy();
        }, ['matching_policy']);
        $this->container->setDefinition('request.matching_policy.route', function($container) {
            return new RouteMatchingPolicy();
        }, ['matching_policy']);

        $this->container->setDefinition('request.matching_policy', function ($container) {
            $matchingPolicy = new ChainMatchingPolicy();
            $matchingPolicies = $container->getServicesByTag('matching_policy');
            foreach ($matchingPolicies as $policy) {
                $matchingPolicy->addMatchingPolicy($policy);
            }

            return $matchingPolicy;
        });
    }

    private function registerConfigResolver()
    {
        $this->container->setStaticDefinition('response.config.resolver', function ($container) {
            return new Resolver(
                $container->getService('response.config.loader.yaml'),
                $container->getService('request.matching_policy')
            );
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
        $configFiles = [
            sprintf('%s/config/%s', $this->container->getParameter('tutu.root_path'), 'config.yml'),
            sprintf('%s/config/%s', $this->container->getParameter('tutu.root_path'), 'config.yml.dist')
        ];

        if ($customPath = getenv('tutu_config')) {
            if (!file_exists($customPath)) {
                throw new \RuntimeException('Custom config file not found at '.$customPath);
            }

            $configFiles = (array) $customPath;
        }

        foreach ($configFiles as $filePath) {
            if ($filePath && file_exists($filePath) && $config = Yaml::parse(file_get_contents($filePath))) {
                return $config;
            }
        }

        return [];
    }
}
