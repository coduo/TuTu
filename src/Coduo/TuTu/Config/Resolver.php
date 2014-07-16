<?php

namespace Coduo\TuTu\Config;

use Coduo\TuTu\Config\Loader\Loader;
use Coduo\TuTu\Request\MatchingPolicy;
use Symfony\Component\HttpFoundation\Request;

class Resolver
{
    /**
     * @var Element[]|array
     */
    protected $configs;

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
            $this->configs[] = Element::fromArray($responseArrayConfig);
        }

        $this->matchingPolicy = $matchingPolicy;
    }

    /**
     * @param Request $request
     * @return null
     */
    public function resolveConfigElement(Request $request)
    {
        foreach ($this->configs as $config) {
            if ($this->matchingPolicy->match($request, $config)) {
                return $config;
            }
        }

        return null;
    }
}
