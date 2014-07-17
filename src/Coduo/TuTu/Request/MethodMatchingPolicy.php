<?php

namespace Coduo\TuTu\Request;

use Coduo\TuTu\Config\Element;
use Symfony\Component\HttpFoundation\Request;

class MethodMatchingPolicy implements MatchingPolicy
{
    /**
     * @param Request $request
     * @param Element $config
     * @return bool
     */
    public function match(Request $request, Element $config)
    {
        if (!count($config->getRequest()->getAllowedMethods())) {
            return true;
        }

        return in_array($request->getMethod(), $config->getRequest()->getAllowedMethods(), true);
    }
}
