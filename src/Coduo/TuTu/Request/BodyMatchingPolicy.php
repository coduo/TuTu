<?php

namespace Coduo\TuTu\Request;

use Coduo\PHPMatcher\Matcher;
use Coduo\TuTu\Config\Element;
use Symfony\Component\HttpFoundation\Request;

class BodyMatchingPolicy implements MatchingPolicy
{
    /**
     * @var \Coduo\PHPMatcher\Matcher
     */
    private $phpMatcher;

    /**
     * @param Request $request
     * @param Element $config
     * @return boolean
     */
    public function match(Request $request, Element $config)
    {
        if (!$config->getRequest()->hasBody()) {
            return true;
        }

        if (!$this->phpMatcher->match($request->getContent(), $config->getRequest()->getBody())) {
            return false;
        }

        return true;
    }

    public function __construct(Matcher $phpMatcher)
    {
        $this->phpMatcher = $phpMatcher;
    }
}
