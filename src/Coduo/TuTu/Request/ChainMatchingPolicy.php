<?php

namespace Coduo\TuTu\Request;

use Coduo\TuTu\Config\Element;
use Symfony\Component\HttpFoundation\Request;

class ChainMatchingPolicy implements MatchingPolicy
{
    /**
     * @var array|MatchingPolicy[]
     */
    private $matchingPolicies;

    public function __construct()
    {
        $this->matchingPolicies = [];
    }

    /**
     * @param Request $request
     * @param Element $config
     * @return bool
     */
    public function match(Request $request, Element $config)
    {
        if (!count($this->matchingPolicies)) {
            return false;
        }

        foreach ($this->matchingPolicies as $matchingPolicy) {
            if (!$matchingPolicy->match($request, $config)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param MatchingPolicy $matchingPolicy
     */
    public function addMatchingPolicy(MatchingPolicy $matchingPolicy)
    {
        $this->matchingPolicies[] = $matchingPolicy;
    }
}
