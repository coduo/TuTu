<?php

namespace Coduo\TuTu\Response\Config;

use Symfony\Component\Yaml\Yaml;

class YamlLoader implements Loader
{
    /**
     * @var string
     */
    private $responsesYamlPath;

    /**
     * @var string
     */
    private $responsesYamlDir;

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
        $this->responsesYamlDir = dirname($this->responsesYamlPath);
    }

    /**
     * @throws \RuntimeException
     * @return array
     */
    public function getResponsesArray()
    {
        $config = Yaml::parse(file_get_contents($this->responsesYamlPath));
        $config = is_array($config) ? $config : [];

        $includes = (array_key_exists('includes', $config)) ? $config['includes'] : [];
        if (count($includes)) {
            unset($config['includes']);

            foreach ($includes as $fileName) {
                $includeFilePath = $this->responsesYamlDir . '/' . $fileName;
                $loader = new YamlLoader($includeFilePath);
                $config = array_merge($config, $loader->getResponsesArray());
            }
        }

        return $config;
    }
}
