<?php

namespace spec\Coduo\TuTu\Request;

use Coduo\TuTu\Config\Element;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;

class MethodMatchingPolicySpec extends ObjectBehavior
{
    function it_is_matching_policy()
    {
        $this->shouldBeAnInstanceOf('Coduo\TuTu\Request\MatchingPolicy');
    }

    function it_return_true_when_config_does_not_have_allowed_methods()
    {
        $this->match(Request::create('/foo'), Element::fromArray(['request' => ['path' => '/foo']]))->shouldReturn(true);
    }

    function it_return_true_when_request_method_is_in_response_config_allowed_methods()
    {
        $responseConfig = Element::fromArray(['request' => ['path' => '/foo', 'methods' => ['POST']]]);
        $request        = Request::create('/foo', 'POST');

        $this->match($request, $responseConfig)->shouldReturn(true);
    }

    function it_return_false_when_request_method_is_not_in_response_config_allowed_methods()
    {
        $responseConfig = Element::fromArray(['request' => ['path' => '/foo', 'methods' => ['POST']]]);
        $request        = Request::create('/foo', 'GET');

        $this->match($request, $responseConfig)->shouldReturn(false);
    }
}
