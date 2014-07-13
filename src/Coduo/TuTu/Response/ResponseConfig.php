<?php

namespace Coduo\TuTu\Response;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Route;

final class ResponseConfig
{
    /**
     * @var
     */
    private $routePattern;

    /**
     * @var array
     */
    private $methods;

    /**
     * @var string
     */
    private $content;

    /**
     * @var int
     */
    private $status;

    /**
     * @var array
     */
    private $headers;
    /**
     * @var \Symfony\Component\Routing\Route
     */
    private $route;

    /**
     * @param $route
     * @param string $content
     * @param int $status
     * @param array $headers
     * @internal param array $methods
     */
    public function __construct(Route $route, $content = '', $status = 200, $headers = [])
    {
        $this->methods = [];
        $this->content = $content;
        $this->status = (int) $status;
        $this->headers = $headers;
        $this->route = $route;
    }

    public static function fromArray(array $arrayConfig)
    {
        $configResolver = self::createArrayConfigResolver();
        $config = $configResolver->resolve($arrayConfig);
        $responseConfig = new ResponseConfig(
            new Route($config['path']),
            $config['content'],
            $config['status'],
            $config['headers']
        );
        $responseConfig->setAllowedMethods($config['methods']);

        return $responseConfig;
    }

    /**
     * @param $methods
     */
    public function setAllowedMethods($methods)
    {
        $this->methods = array_map(function ($method) {
            return strtoupper($method);
        }, $methods);
    }

    /**
     * @param $method
     * @return bool
     */
    public function isMethodAllowed($method)
    {
        if (!count($this->methods)) {
            return true;
        }

        return in_array(strtoupper($method), $this->methods, true);
    }

    /**
     * @param $route
     * @return bool
     */
    public function routeMatch($route)
    {
        return 0 !== preg_match($this->routePattern, $this->trimRoute($route));
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return \Symfony\Component\Routing\Route
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @return OptionsResolver
     */
    private static function createArrayConfigResolver()
    {
        $configResolver = new OptionsResolver();
        $configResolver->setRequired(['path']);
        $configResolver->setDefaults(['content' => '', 'status' => 200, 'headers' => [], 'methods' => []]);
        $configResolver->setAllowedTypes([
            'path' => 'string',
            'content' => 'string',
            'status' => 'integer',
            'headers' => 'array',
            'methods' => 'array'
        ]);

        return $configResolver;
    }
}
