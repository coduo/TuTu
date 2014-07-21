<?php

namespace Coduo\TuTu\Request;

use Coduo\TuTu\Config\Element;
use Symfony\Component\HttpFoundation\Request;

class HeadersMatchingPolicy implements MatchingPolicy
{
    /**
     * @param Request $request
     * @param Element $config
     * @return boolean
     */
    public function match(Request $request, Element $config)
    {
        if (!$config->getRequest()->hasHeaders()) {
            return true;
        }

        foreach ($config->getRequest()->getHeaders() as $headerName => $headerValue) {
            if (!$request->headers->has($headerName)) {
                return false;
            }

            if ($request->headers->get($headerName) !== $headerValue) {
                return false;
            }
        }

        return true;
    }
}
