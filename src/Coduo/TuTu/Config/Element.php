<?php

namespace Coduo\TuTu\Config;

use Coduo\TuTu\Config\Element\Request;
use Coduo\TuTu\Config\Element\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class Element
{
    /**
     * @var Element\Request
     */
    private $request;

    /**
     * @var Element\Response
     */
    private $response;

    /**
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @param array $arrayConfig
     * @return Element
     */
    public static function fromArray(array $arrayConfig)
    {
        $configResolver = self::createArrayConfigResolver();
        $config = $configResolver->resolve($arrayConfig);
        $responseConfig = new Element(
            Request::fromArray($config['request']),
            Response::fromArray($config['response'])
        );

        return $responseConfig;
    }

    /**
     * @return \Coduo\TuTu\Config\Element\Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return \Coduo\TuTu\Config\Element\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return OptionsResolver
     */
    private static function createArrayConfigResolver()
    {
        $configResolver = new OptionsResolver();
        $configResolver->setRequired(['request']);
        $configResolver->setDefaults(['response' => []]);
        $configResolver->setAllowedTypes([
            'request' => 'array',
            'response' => 'array',
        ]);

        return $configResolver;
    }
}
