<?php

namespace Coduo\TuTu\Request;

use Coduo\PHPMatcher\Matcher;
use Coduo\TuTu\Config\Element;
use Symfony\Component\HttpFoundation\Request;

class ParameterMatchingPolicy implements MatchingPolicy
{
    /**
     * @var \Coduo\PHPMatcher\Matcher
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
        if (!$config->getRequest()->hasBodyParameters() && !$config->getRequest()->hasQueryParameters()) {
            return true;
        }

        foreach ($config->getRequest()->getQueryParameters() as $name => $valuePattern) {
            if (!$request->query->has($name)) {
                return false;
            }
            if (!$this->phpMatcher->match($request->query->get($name), $valuePattern)) {
                return false;
            }
        }

        foreach ($config->getRequest()->getBodyParameters() as $name => $valuePattern) {
            if (!$request->request->has($name)) {
                return false;
            }

            if (!$this->phpMatcher->match($request->request->get($name), $valuePattern)) {
                return false;
            }
        }

        return true;
    }
}
