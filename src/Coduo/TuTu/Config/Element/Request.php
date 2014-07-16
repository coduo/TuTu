<?php

namespace Coduo\TuTu\Config\Element;

use Symfony\Component\OptionsResolver\OptionsResolver;

class Request
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    private $allowedMethods;

    /**
     * @param $path
     */
    public function __construct($path)
    {
        $this->validatePath($path);
        $this->path = $path;
        $this->allowedMethods = [];
    }

    public static function fromArray(array $arrayConfig)
    {
        $configResolver = self::createArrayConfigResolver();
        $config = $configResolver->resolve($arrayConfig);
        $responseConfig = new Request($config['path']);

        $responseConfig->setAllowedMethods($config['methods']);

        return $responseConfig;
    }

    /**
     * @param $methods
     */
    public function setAllowedMethods($methods)
    {
        $this->allowedMethods = array_map(function ($method) {
            return strtoupper($method);
        }, $methods);
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return $this->allowedMethods;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return '/' . ltrim($this->path, '/');
    }

    /**
     * @param $path
     * @throws \InvalidArgumentException
     */
    private function validatePath($path)
    {
        if (!is_string($path)) {
            throw new \InvalidArgumentException("Path must be a valid string.");
        }

        if (empty($path)) {
            throw new \InvalidArgumentException("Path can't be empty.");
        }
    }

    /**
     * @return OptionsResolver
     */
    private static function createArrayConfigResolver()
    {
        $configResolver = new OptionsResolver();
        $configResolver->setRequired(['path']);
        $configResolver->setDefaults(['methods' => []]);
        $configResolver->setAllowedTypes([
            'path' => 'string',
            'methods' => 'array'
        ]);

        return $configResolver;
    }
}
