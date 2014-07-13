<?php

namespace Coduo\TuTu\Response;

use Coduo\TuTu\Request\MatchingPolicy;
use Coduo\TuTu\Response\Config\Loader;
use Symfony\Component\HttpFoundation\Request;

class ConfigResolver
{
    /**
     * @var ResponseConfig[]|array
     */
    protected $configs;

    /**
     * @var \Symfony\Component\Routing\RouteCollection
     */
    protected $routeCollection;
    /**
     * @var \Coduo\TuTu\Request\MatchingPolicy
     */
    private $matchingPolicy;

    /**
     * @param Loader $loader
     * @param MatchingPolicy $matchingPolicy
     */
    public function __construct(Loader $loader, MatchingPolicy $matchingPolicy)
    {
        $this->configs = [];
        foreach ($loader->getResponsesArray() as $responseArrayConfig) {
            $this->configs[] = ResponseConfig::fromArray($responseArrayConfig);
        }

        $this->matchingPolicy = $matchingPolicy;
    }

    /**
     * @param Request $request
     * @return null
     */
    public function resolveResponseConfig(Request $request)
    {
        foreach ($this->configs as $config) {
            if ($this->matchingPolicy->match($request, $config)) {
                return $config;
            }
        }

        return null;
    }
}
