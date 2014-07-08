<?php

namespace Coduo\TuTu\Response;

class ResponseConfig
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
     * @param $route
     * @param array $methods
     * @param string $content
     * @param int $status
     * @param array $headers
     */
    public function __construct($route, array $methods = [], $content = '', $status = 200, $headers = [])
    {
        $this->validateRoute($route);

        $routePattern = $this->buildRoutePattern($route);
        $this->routePattern = $routePattern;
        $this->methods = array_map(function ($method) {
            return strtoupper($method);
        }, $methods);
        $this->content = $content;
        $this->status = (int) $status;
        $this->headers = $headers;
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
     * @param $route
     * @return string
     */
    private function trimRoute($route)
    {
        return trim($route, '/');
    }

    /**
     * @param $route
     * @throws \InvalidArgumentException
     */
    private function validateRoute($route)
    {
        if (!is_string($route)) {
            throw new \InvalidArgumentException("Route must be a valid string.");
        }

        if (empty($route)) {
            throw new \InvalidArgumentException("Route can't be empty.");
        }
    }

    /**
     * @param $route
     * @return mixed|string
     */
    private function buildRoutePattern($route)
    {
        $routePattern = preg_replace('/{id}/', '__PLACEHOLDER__', $this->trimRoute($route));
        return '/^' . preg_replace('/__PLACEHOLDER__/', '([^\/]*)', preg_quote($routePattern, '/')) . '$/';;
    }
}
