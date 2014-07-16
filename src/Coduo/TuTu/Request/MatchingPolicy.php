<?php

namespace Coduo\TuTu\Request;

use Coduo\TuTu\Config\Element;
use Symfony\Component\HttpFoundation\Request;

interface MatchingPolicy
{
    /**
     * @param Request $request
     * @param Element $config
     * @return boolean
     */
    public function match(Request $request, Element $config);
}
