<?php

namespace Coduo\TuTu\Request;

use Coduo\TuTu\Response\ResponseConfig;
use Symfony\Component\HttpFoundation\Request;

class RouteMatchingPolicy implements MatchingPolicy
{
    /**
     * @param Request $request
     * @param ResponseConfig $responseConfig
     * @return boolean
     */
    public function match(Request $request, ResponseConfig $responseConfig)
    {
        $pathPattern = $responseConfig->getPath();
        preg_match_all('#\{\w+\}#', $responseConfig->getPath(), $placeholders, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

        foreach ($placeholders as $placeholderMatch) {
            $placeholder = $placeholderMatch[0][0];
            $pathPattern = str_replace($placeholder, '__PLACEHOLDER__', $pathPattern);
        }

        $pathPattern = '/^' . str_replace('__PLACEHOLDER__', '([^\/]*)', preg_quote($pathPattern, '/')) . '$/i';

        return 0 !== preg_match($pathPattern, $request->getPathInfo());
    }
}
