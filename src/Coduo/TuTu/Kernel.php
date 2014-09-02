<?php

namespace Coduo\TuTu;

use Coduo\PHPMatcher\Factory\SimpleFactory;
use Coduo\TuTu\Config\Loader\YamlLoader;
use Coduo\TuTu\Config\Resolver;
use Coduo\TuTu\Event\PreConfigResolve;
use Coduo\TuTu\Event\RequestMatch;
use Coduo\TuTu\Extension\Initializer;
use Coduo\TuTu\Request\BodyMatchingPolicy;
use Coduo\TuTu\Request\ChainMatchingPolicy;
use Coduo\TuTu\Request\HeadersMatchingPolicy;
use Coduo\TuTu\Request\MethodMatchingPolicy;
use Coduo\TuTu\Request\ParameterMatchingPolicy;
use Coduo\TuTu\Request\Path\Parser;
use Coduo\TuTu\Request\RouteMatchingPolicy;
use Coduo\TuTu\Response\Builder;
use Symfony\Component\ClassLoader\ClassLoader;
use Symfony\Component\EventDispatcher\EventDispatcher;
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
            $request = $this->dispatchPreConfigResolveEvent($request);
            $configElement = $this->container->getService('response.config.resolver')->resolveConfigElement($request);
            if (isset($configElement)) {
                $this->dispatchRequestMatchEvent($configElement);

                return $this->container->getService('response.builder')->build($configElement, $request);
            }
            return $this->container->getService('response.builder')->buildForMismatch($request);
        } catch (\Exception $e) {
            return $this->container->getService('response.builder')->buildForException($e);
        }
    }

    private function setUpContainer()
    {
        $this->registerClassLoader();
        $this->registerPHPMatcher();
        $this->registerTwig();
        $this->registerEventDispatcher();
        $this->registerExtensionInitializer();
        $this->registerConfigLoader();
        $this->registerRequestMatchingPolicy();
        $this->registerConfigResolver();
        $this->registerResponseBuilder();
    }

    private function registerClassLoader()
    {
        $this->container->setStaticDefinition('class_loader', function ($container) {
            return new ClassLoader();
        });
    }

    private function registerPHPMatcher()
    {
        $this->container->setStaticDefinition('php_matcher', function ($container) {
            return (new SimpleFactory())->createMatcher();
        });
    }

    private function registerTwig()
    {
        $resourcesPath = $this->container->getParameter('tutu.root_path') . '/resources';

        if ($customPath = getenv('tutu_resources')) {
            if (!file_exists($customPath)) {
                throw new \RuntimeException(sprintf('Custom resources path \"%s\" does not exist.', $customPath));
            }
            $resourcesPath = $customPath;
        }

        $this->container->setParameter(
            'resources_path',
            $resourcesPath
        );

        $this->container->setStaticDefinition('twig_loader', function ($container) {
            $stringLoader = new \Twig_Loader_String();
            $filesystemLoader = new \Twig_Loader_Filesystem();
            $filesystemLoader->addPath($container->getParameter('resources_path'), 'resources');
            return new \Twig_Loader_Chain([$filesystemLoader, $stringLoader]);
        });

        $this->container->setStaticDefinition('twig' ,function ($container) {
            $defaultOptions = ['cache' => $container->getParameter('tutu.root_path') . '/var/twig'];
            $options = $container->hasParameter('twig') && is_array($container->getParameter('twig'))
                ? array_merge($defaultOptions, $container->getParameter('twig'))
                : $defaultOptions;

            $twig = new \Twig_Environment($container->getService('twig_loader'), $options);

            return $twig;
        });
    }

    private function registerEventDispatcher()
    {
        $this->container->setStaticDefinition('event_dispatcher' , function ($container) {
            $eventDispatcher = new EventDispatcher();
            $eventSubscribers = $container->getServicesByTag('event_dispatcher.subscriber');
            foreach ($eventSubscribers as $subscriber) {
                $eventDispatcher->addSubscriber($subscriber);
            }

            return $eventDispatcher;
        });
    }

    private function registerExtensionInitializer()
    {
        $this->container->setDefinition('extension.initializer', function ($container) {
            return new Initializer($this->container->getService('class_loader'));
        });
    }

    private function registerConfigLoader()
    {
        $responsesPath = $this->container->getParameter('tutu.root_path') . '/config/responses.yml';

        if ($customPath = getenv('tutu_responses')) {
            if (!file_exists($customPath)) {
                throw new \RuntimeException('Custom responses file not found at ' . $customPath);
            }
            $responsesPath = $customPath;
        }

        $this->container->setParameter(
            'responses_file_path',
            $responsesPath
        );

        $this->container->setDefinition('response.config.loader.yaml', function ($container) {
            return new YamlLoader($container->getParameter('responses_file_path'));
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
        $this->container->setDefinition('request.matching_policy.parameter', function($container) {
            return new ParameterMatchingPolicy($container->getService('php_matcher'));
        }, ['matching_policy']);
        $this->container->setDefinition('request.matching_policy.headers', function($container) {
            return new HeadersMatchingPolicy($container->getService('php_matcher'));
        }, ['matching_policy']);
        $this->container->setDefinition('request.matching_policy.body', function($container) {
            return new BodyMatchingPolicy($container->getService('php_matcher'));
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
        $this->container->setDefinition('request.path.parser', function($container) {
            return new Parser();
        });
        $this->container->setDefinition('response.builder', function ($container) {
            return new Builder($container->getService('twig'), $container->getService('request.path.parser'));
        });
    }

    private function loadConfiguration()
    {
        $config = $this->parseConfiguration();
        if (array_key_exists('autoload', $config)) {
            $this->container->getService('class_loader')->addPrefixes($config['autoload']);
        }

        if (array_key_exists('parameters', $config)) {
            if (!is_array($config['parameters'])) {
                throw new \RuntimeException("Parameters key in config.yml must be a valid array.");
            }

            foreach ($config['parameters'] as $id => $value) {
                $this->container->setParameter($id, $value);
            }
        }

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

    /**
     * @param $configElement
     */
    private function dispatchRequestMatchEvent($configElement)
    {
        $this->container->getService('event_dispatcher')->dispatch(
            Events::REQUEST_MATCH,
            new RequestMatch($configElement)
        );
    }

    /**
     * @param Request $request
     * @return Request
     */
    private function dispatchPreConfigResolveEvent(Request $request)
    {
        $event = new PreConfigResolve($request);
        $this->container->getService('event_dispatcher')->dispatch(
            Events::PRE_CONFIG_RESOLVE,
            $event
        );

        return $event->getRequest();
    }
}
