<?php

namespace Coduo\TuTu\Response\Config;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Yaml\Yaml;

class YamlLoader implements Loader
{
    private $responsesYamlPath;

    private $config;

    /**
     * @param $responsesYamlPath
     * @throws \InvalidArgumentException
     * @internal param $ [] $yamlFileContent
     */
    public function __construct($responsesYamlPath)
    {
        if (!file_exists($responsesYamlPath)) {
            throw new \InvalidArgumentException(sprintf("File \"%s\" does not exist.", $responsesYamlPath));
        }

        $this->responsesYamlPath = $responsesYamlPath;
        $this->config = Yaml::parse(file_get_contents($this->responsesYamlPath));
    }

    /**
     * @{inheritDoc}
     */
    public function getResponsesArray()
    {
        return is_array($this->config) ? $this->config : [];
    }

    /**
     * @{inheritDoc}
     */
    public function getRouteCollection()
    {
        $routeCollection = new RouteCollection();

        if (count($this->config) > 0) {
            foreach ($this->config as $name => $params) {
                $routeCollection->add($name, new Route($params['path']));
            }
        }

        return $routeCollection;
    }
}
