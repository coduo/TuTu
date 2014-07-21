<?php

namespace Coduo\TuTu\Request;

use Coduo\TuTu\Config\Element;
use Symfony\Component\HttpFoundation\Request;

class ParameterMatchingPolicy implements MatchingPolicy
{
    /**
     * @param Request $request
     * @param Element $config
     * @return boolean
     */
    public function match(Request $request, Element $config)
    {
        if (!$config->getRequest()->hasBodyParameters() && !$config->getRequest()->hasQueryParameters()) {
            return true;
        }

        foreach ($config->getRequest()->getQueryParameters() as $name => $value) {
            if (!$request->query->has($name)) {
                return false;
            }
            if ($request->query->get($name) !== $value) {
                return false;
            }
        }

        foreach ($config->getRequest()->getBodyParameters() as $name => $value) {
            if (!$request->request->has($name)) {
                return false;
            }

            if ($request->request->get($name) !== $value) {
                return false;
            }
        }

        return true;
    }
}
