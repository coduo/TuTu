<?php

namespace Coduo\TuTu\Response;

class Config
{
    const INDEX_PATH = 'path';
    const INDEX_METHOD = 'methods';
    const INDEX_CONTENT = 'content';
    const INDEX_STATUS = 'status';
    const INDEX_HEADERS = 'headers';

    /**
     * @var array
     */
    private $configuration;

    /**
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getConfiguration()
    {
        $responses = [];
        foreach ($this->configuration as $responseName => $responseArrayConfig) {
            if (!is_array($responseArrayConfig)) {
                throw new \RuntimeException("Each response must be a valid array.");
            }

            if (!array_key_exists(self::INDEX_PATH, $responseArrayConfig)) {
                throw new \RuntimeException(sprintf("Cant find \"path\" index under \"%s\" response configuration.", $responseName));
            }

            $methods = (array_key_exists(self::INDEX_METHOD, $responseArrayConfig)) ? $responseArrayConfig[self::INDEX_METHOD] : [];
            if (!is_array($methods)) {
                throw new \RuntimeException(sprintf("Allowed methods under \"%s\" must be passed as a valid array.", $responseName));
            }

            $headers = (array_key_exists(self::INDEX_HEADERS, $responseArrayConfig)) ? $responseArrayConfig[self::INDEX_HEADERS] : [];
            if (!is_array($headers)) {
                throw new \RuntimeException(sprintf("Response headers under \"%s\" must be passed as a valid array.", $responseName));
            }

            $responses[] = new ResponseConfig(
                $responseArrayConfig[self::INDEX_PATH],
                $methods,
                array_key_exists(self::INDEX_CONTENT, $responseArrayConfig) ? $responseArrayConfig[self::INDEX_CONTENT] : '',
                array_key_exists(self::INDEX_STATUS, $responseArrayConfig) ? $responseArrayConfig[self::INDEX_STATUS] : 200,
                $headers
            );
        }

        return $responses;
    }
}
