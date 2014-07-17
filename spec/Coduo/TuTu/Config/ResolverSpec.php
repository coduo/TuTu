<?php

namespace spec\Coduo\TuTu\Config;

use Coduo\TuTu\Config\Loader\Loader;
use Coduo\TuTu\Request\MatchingPolicy;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;

class ResolverSpec extends ObjectBehavior
{
    function let(Loader $loader, MatchingPolicy $matchingPolicy)
    {
        $loader->getResponsesArray()->willReturn([]);
        $this->beConstructedWith($loader, $matchingPolicy);
    }

    function it_resolve_config_when_matching_policy_match_request_to_response_config(Loader $loader, MatchingPolicy $matchingPolicy)
    {
        $loader->getResponsesArray()->willReturn([
            [
                'request' => [
                    'path' => '/foo/index',
                    'methods' => ['POST']
                ]
            ]
        ]);

        $request = Request::create('/foo/index', 'POST');
        $matchingPolicy->match($request, Argument::type('Coduo\TuTu\Config\Element'))->willReturn(true);

        $this->resolveConfigElement($request)->shouldReturnAnInstanceOf('Coduo\TuTu\Config\Element');
    }

    function it_return_null_when_matching_policy_cant_match_request(MatchingPolicy $matchingPolicy)
    {
        $request = Request::create('/foo/index', 'POST');
        $matchingPolicy->match($request, Argument::type('Coduo\TuTu\Config\Element'))->willReturn(false);

        $this->resolveConfigElement($request)->shouldReturn(null);
    }
}
