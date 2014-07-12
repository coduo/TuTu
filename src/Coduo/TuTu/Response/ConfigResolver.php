<?php

namespace Coduo\TuTu\Response;

use Coduo\TuTu\Response\Config\Loader;
use Symfony\Component\HttpFoundation\Request;

class ConfigResolver
{
    /**
     * @var ResponseConfig[]|array
     */
    protected $configs;

    public function __construct(Loader $loader)
    {
        $this->configs = [];
        foreach ($loader->getResponsesArray() as $responseArrayConfig) {
            $this->configs[] = ResponseConfig::fromArray($responseArrayConfig);
        }
    }

    /**
     * @param Request $request
     * @return null
     */
    public function resolveResponseConfig(Request $request)
    {
        foreach ($this->configs as $config) {
            if ($config->isMethodAllowed($request->getMethod())) {
                if ($config->routeMatch($request->getPathInfo())) {
                    return $config;
                }
            }
        }

        return null;
    }
}
