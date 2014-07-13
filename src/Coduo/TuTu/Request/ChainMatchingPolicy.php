<?php

namespace Coduo\TuTu\Request;

use Coduo\TuTu\Response\ResponseConfig;
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
     * @param ResponseConfig $responseConfig
     * @return boolean
     */
    public function match(Request $request, ResponseConfig $responseConfig)
    {
        if (!count($this->matchingPolicies)) {
            return false;
        }

        foreach ($this->matchingPolicies as $matchingPolicy) {
            if (!$matchingPolicy->match($request, $responseConfig)) {
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
