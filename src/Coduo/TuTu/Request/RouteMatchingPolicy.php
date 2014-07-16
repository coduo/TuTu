<?php

namespace Coduo\TuTu\Request;

use Coduo\TuTu\Config\Element;
use Symfony\Component\HttpFoundation\Request;

class RouteMatchingPolicy implements MatchingPolicy
{
    /**
     * @param Request $request
     * @param Element $config
     * @return bool
     */
    public function match(Request $request, Element $config)
    {
        $pathPattern = $config->getRequest()->getPath();
        preg_match_all('#\{\w+\}#', $config->getRequest()->getPath(), $placeholders, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

        foreach ($placeholders as $placeholderMatch) {
            $placeholder = $placeholderMatch[0][0];
            $pathPattern = str_replace($placeholder, '__PLACEHOLDER__', $pathPattern);
        }

        $pathPattern = '/^' . str_replace('__PLACEHOLDER__', '([^\/]*)', preg_quote($pathPattern, '/')) . '$/i';

        return 0 !== preg_match($pathPattern, $request->getPathInfo());
    }
}
