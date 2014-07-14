<?php

namespace Coduo\TuTu\Request;

use Coduo\TuTu\Response\ResponseConfig;
use Symfony\Component\HttpFoundation\Request;

interface MatchingPolicy
{
    /**
     * @param Request $request
     * @param ResponseConfig $responseConfig
     * @return boolean
     */
    public function match(Request $request, ResponseConfig $responseConfig);
}
