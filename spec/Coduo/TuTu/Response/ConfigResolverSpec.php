<?php

namespace spec\Coduo\TuTu\Response;

use Coduo\TuTu\Request\MatchingPolicy;
use Coduo\TuTu\Response\Config\Loader;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;

class ConfigResolverSpec extends ObjectBehavior
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
                'path' => '/foo/index',
                'methods' => ['POST']
            ]
        ]);

        $request = Request::create('/foo/index', 'POST');
        $matchingPolicy->match($request, Argument::type('Coduo\TuTu\Response\ResponseConfig'))->willReturn(true);

        $this->resolveResponseConfig($request)->shouldReturnAnInstanceOf('Coduo\TuTu\Response\ResponseConfig');
    }

    function it_return_null_when_matching_policy_cant_match_request(MatchingPolicy $matchingPolicy)
    {
        $request = Request::create('/foo/index', 'POST');
        $matchingPolicy->match($request, Argument::type('Coduo\TuTu\Response\ResponseConfig'))->willReturn(false);

        $this->resolveResponseConfig($request)->shouldReturn(null);
    }
}
