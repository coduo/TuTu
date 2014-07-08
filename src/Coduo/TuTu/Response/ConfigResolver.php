<?php

namespace Coduo\TuTu\Response;

use Symfony\Component\HttpFoundation\Request;

class ConfigResolver
{
    /**
     * @var ResponseConfig[]|array
     */
    protected $configs;

    public function __construct()
    {
        $this->configs = [];
    }

    /**
     * @param ResponseConfig $config
     */
    public function addResponseConfig(ResponseConfig $config)
    {
        $this->configs[] = $config;
    }

    /**
     * @param Request $request
     * @return null
     */
    public function resolveResponse(Request $request)
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
