<?php

namespace Coduo\TuTu\Response\Config;

use Coduo\TuTu\Response\ResponseConfig;
use Symfony\Component\Yaml\Yaml;

class YamlLoader
{
    private $configYamlPath;

    /**
     * @param [] $yamlFileContent
     */
    public function __construct($applicationRootPath)
    {
        $this->configYamlPath = $applicationRootPath . '/config/responses.yml';
    }

    public function getConfigurationArray()
    {
        if (file_exists($this->configYamlPath)) {
            return Yaml::parse($this->configYamlPath);
        }

        return [];
    }
}
