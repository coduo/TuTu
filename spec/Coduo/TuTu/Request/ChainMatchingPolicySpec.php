<?php

namespace spec\Coduo\TuTu\Request;

use Coduo\TuTu\Config\Element;
use Coduo\TuTu\Request\MatchingPolicy;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;

class ChainMatchingPolicySpec extends ObjectBehavior
{
    function it_returns_false_by_default()
    {
        $this->match(Request::create('/foo'), Element::fromArray(['request' => ['path' => '/foo']]))->shouldReturn(false);
    }

    function it_returns_true_when_all_policies_can_match_request_to_response_config(
        MatchingPolicy $positiveMatchingPolicy1,
        MatchingPolicy $positiveMatchingPolicy2
    ) {
        $this->addMatchingPolicy($positiveMatchingPolicy1);
        $this->addMatchingPolicy($positiveMatchingPolicy2);

        $responseConfig = Element::fromArray(['request' => ['path' => '/foo']]);
        $request        = Request::create('/foo');

        $positiveMatchingPolicy1->match($request, $responseConfig)->willReturn(true);
        $positiveMatchingPolicy2->match($request, $responseConfig)->willReturn(true);

        $this->match($request, $responseConfig)->shouldReturn(true);
    }
}
