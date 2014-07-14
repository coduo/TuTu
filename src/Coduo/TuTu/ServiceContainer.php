<?php

namespace Coduo\TuTu;

/**
 * Simple service container inspired by PhpSpec/ServiceContainer class which was created on top of Pimple.
 */
class ServiceContainer
{
    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var array
     */
    private $serviceDefinitions = [];

    /**
     * @var array
     */
    private $tags = [];

    /**
     * @param $id
     * @return bool
     */
    public function hasParameter($id)
    {
        return array_key_exists($id, $this->parameters);
    }

    /**
     * @param $id
     * @param $value
     */
    public function setParameter($id, $value)
    {
        $this->parameters[$id] = $value;
    }

    /**
     * @param $id
     * @return mixed
     * @throws \RuntimeException
     */
    public function getParameter($id)
    {
        if (!array_key_exists($id, $this->parameters)) {
            throw new \RuntimeException(sprintf("Service container does not have parameter with id \"%s\"", $id));
        }

        return $this->parameters[$id];
    }

    /**
     * @param $id
     * @return bool
     */
    public function hasService($id)
    {
        return array_key_exists($id, $this->serviceDefinitions);
    }


    public function removeService($id)
    {
        if ($this->hasService($id)){
            unset($this->serviceDefinitions[$id]);
        }
    }

    /**
     * getService($id) will return result of $definition closure.
     * Callback will be executed with $this (ServiceContainer) as a argument.
     *
     * @param $id
     * @param callable $definition
     * @param array $tags
     */
    public function setDefinition($id, \Closure $definition, $tags = [])
    {
        $this->serviceDefinitions[$id] = $definition;
        $this->tags[$id] = $tags;
    }

    /**
     * Works just like setDefinition but getService($id) is going to return
     * exactly same value every single time.
     *
     * @param $id
     * @param callable $definition
     * @param array $tags
     */
    public function setStaticDefinition($id, \Closure $definition, $tags = [])
    {
        $this->setDefinition($id, function ($container) use ($definition) {
            static $instance;
            if (!isset($instance)) {
                $instance = $definition($container);
            }

            return $instance;
        }, $tags);
    }

    /**
     * @param $id
     * @return mixed
     * @throws \RuntimeException
     */
    public function getService($id)
    {
        if (!array_key_exists($id, $this->serviceDefinitions)) {
            throw new \RuntimeException("Service container does not have service with id \"key\"");
        }

        return $this->serviceDefinitions[$id]($this);
    }

    /**
     * @param $tag
     * @return array
     */
    public function getServicesByTag($tag)
    {
        $services = [];
        foreach ($this->tags as $serviceId => $tags) {
            if (in_array($tag, $tags, true)) {
                $services[] = $this->getService($serviceId);
            }
        }

        return $services;
    }
}
