<?php

namespace Coduo\TuTu\Response;

use Symfony\Component\OptionsResolver\OptionsResolver;

final class ResponseConfig
{
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
    private $path;

    /**
     * @param $path
     * @param string $content
     * @param int $status
     * @param array $headers
     * @internal param array $methods
     */
    public function __construct($path, $content = '', $status = 200, $headers = [])
    {
        $this->validatePath($path);
        $this->methods = [];
        $this->content = $content;
        $this->status = (int) $status;
        $this->headers = $headers;
        $this->path = $path;
    }

    public static function fromArray(array $arrayConfig)
    {
        $configResolver = self::createArrayConfigResolver();
        $config = $configResolver->resolve($arrayConfig);
        $responseConfig = new ResponseConfig(
            $config['path'],
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
     * @return array
     */
    public function getAllowedMethods()
    {
        return $this->methods;
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
