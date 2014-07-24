<?php

namespace Coduo\TuTu\Request;

use Coduo\PHPMatcher\Matcher;
use Coduo\TuTu\Config\Element;
use Symfony\Component\HttpFoundation\Request;

class HeadersMatchingPolicy implements MatchingPolicy
{
    /**
     * @var \Coduo\PHPMatcher\Matcher\Matcher
     */
    private $phpMatcher;

    public function __construct(Matcher $phpMatcher)
    {
        $this->phpMatcher = $phpMatcher;
    }

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

        foreach ($config->getRequest()->getHeaders() as $headerName => $headerValuePattern) {
            if (!$request->headers->has($headerName)) {
                return false;
            }

            if (!$this->phpMatcher->match($request->headers->get($headerName), $headerValuePattern)) {
                return false;
            }
        }

        return true;
    }
}
