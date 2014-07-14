<?php

namespace Coduo\TuTu\Request;

use Coduo\TuTu\Response\ResponseConfig;
use Symfony\Component\HttpFoundation\Request;

class MethodMatchingPolicy implements MatchingPolicy
{
    /**
     * @param Request $request
     * @param ResponseConfig $responseConfig
     * @return boolean
     */
    public function match(Request $request, ResponseConfig $responseConfig)
    {
        if (!count($responseConfig->getAllowedMethods())) {
            return true;
        }

        return in_array($request->getMethod(), $responseConfig->getAllowedMethods(), true);
    }
}
