<?php

namespace Coduo\TuTu\Response;

use Coduo\TuTu\Response\Config\Loader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

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

    public function __construct(Loader $loader)
    {
        $this->configs = [];
        foreach ($loader->getResponsesArray() as $name => $responseArrayConfig) {
            $this->configs[$name] = ResponseConfig::fromArray($responseArrayConfig);
        }

        $this->routeCollection = $this->buildRouteCollection();
    }

    /**
     * @param Request $request
     * @return null
     */
    public function resolveResponseConfig(Request $request)
    {
        $context = new RequestContext();
        $context->fromRequest($request);
        $matcher = new UrlMatcher($this->routeCollection, $context);

        foreach ($this->configs as $name => $config) {
            if ($config->isMethodAllowed($request->getMethod())) {

                if ($matcher->matchRequest($request)) {
                    return $this->configs[$name];
                }
            }
        }

        return null;
    }

    /**
     * @{inheritDoc}
     */
    private function buildRouteCollection()
    {
        $routeCollection = new RouteCollection();

        if (count($this->configs) > 0) {
            foreach ($this->configs as $name => $config) {
                $routeCollection->add($name, $config->getRoute());
            }
        }

        return $routeCollection;
    }
}
