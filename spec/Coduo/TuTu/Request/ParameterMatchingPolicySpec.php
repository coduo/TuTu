<?php

namespace spec\Coduo\TuTu\Request;

use Coduo\PHPMatcher\Factory\SimpleFactory;
use Coduo\TuTu\Config\Element;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;

class ParameterMatchingPolicySpec extends ObjectBehavior
{
    function let()
    {
        $phpMatcher = (new SimpleFactory())->createMatcher();
        $this->beConstructedWith($phpMatcher);
    }

    function it_is_matching_policy()
    {
        $this->shouldBeAnInstanceOf('Coduo\TuTu\Request\MatchingPolicy');
    }

    function it_match_when_request_config_body_and_query_params_are_empty()
    {
        $responseConfig = Element::fromArray([
            'request' => [
                'path' => '/foo',
                'request' => [],
                'query' => []
            ],
        ]);
        $request = Request::create('/foo');

        $this->match($request, $responseConfig)->shouldReturn(true);
    }

    function it_not_match_when_request_config_query_parameter_does_not_exist_in_request_query()
    {
        $responseConfig = Element::fromArray([
            'request' => [
                'path' => '/foo',
                'query' => [
                    'foo' => 'bar'
                ]
            ],
        ]);
        $request = Request::create('/foo');

        $this->match($request, $responseConfig)->shouldReturn(false);
    }

    function it_not_match_when_request_config_query_parameter_is_different_than_request_query_param()
    {
        $responseConfig = Element::fromArray([
            'request' => [
                'path' => '/foo',
                'query' => [
                    'foo' => 'bar'
                ]
            ],
        ]);
        $request = Request::create('/foo?foo=baz');

        $this->match($request, $responseConfig)->shouldReturn(false);
    }

    function it_not_match_when_request_config_body_parameter_does_not_exist_in_request_body()
    {
        $responseConfig = Element::fromArray([
            'request' => [
                'path' => '/foo',
                'request' => [
                    'foo' => 'bar'
                ]
            ],
        ]);
        $request = Request::create('/foo', 'POST', []);

        $this->match($request, $responseConfig)->shouldReturn(false);
    }

    function it_not_match_when_request_config_body_parameter_is_different_than_request_body_param()
    {
        $responseConfig = Element::fromArray([
            'request' => [
                'path' => '/foo',
                'request' => [
                    'foo' => 'bar'
                ]
            ],
        ]);
        $request = Request::create('/foo', 'POST', ['foo' => 'baz']);

        $this->match($request, $responseConfig)->shouldReturn(false);
    }


    function it_match_when_request_config_query_and_body_parameters_match_parameters_from_request()
    {
        $responseConfig = Element::fromArray([
            'request' => [
                'path' => '/foo',
                'query' => [
                    'foo' => 'bar'
                ],
                'request' => [
                    'foo' => 'bar'
                ]
            ],
        ]);
        $request = Request::create('/foo?foo=bar', 'POST', ['foo' => 'bar']);

        $this->match($request, $responseConfig)->shouldReturn(true);
    }
}
