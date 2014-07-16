<?php

namespace Coduo\TuTu\Config\Element;

use Symfony\Component\OptionsResolver\OptionsResolver;

class Response
{
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

    public function __construct($content = '', $status = 200, $headers = [])
    {
        $this->content = $content;
        $this->status = $status;
        $this->headers = $headers;
    }

    public static function fromArray(array $arrayConfig)
    {
        $configResolver = self::createArrayResponseResolver();
        $config = $configResolver->resolve($arrayConfig);
        $responseConfig = new Response(
            $config['content'],
            $config['status'],
            $config['headers']
        );

        return $responseConfig;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
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
     * @return OptionsResolver
     */
    private static function createArrayResponseResolver()
    {
        $configResolver = new OptionsResolver();
        $configResolver->setDefaults(['content' => '', 'status' => 200, 'headers' => []]);
        $configResolver->setAllowedTypes([
            'content' => 'string',
            'status' => 'integer',
            'headers' => 'array',
        ]);

        return $configResolver;
    }
}
