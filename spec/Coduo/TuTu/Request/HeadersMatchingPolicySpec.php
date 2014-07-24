<?php

namespace spec\Coduo\TuTu\Request;

use Coduo\PHPMatcher\Factory\SimpleFactory;
use Coduo\TuTu\Config\Element;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;

class HeadersMatchingPolicySpec extends ObjectBehavior
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

    function it_match_when_request_config_does_not_have_headers()
    {
        $responseConfig = Element::fromArray([
            'request' => [
                'path' => '/foo',
                'headers' => []
            ],
        ]);
        $request = Request::create('/foo');

        $this->match($request, $responseConfig)->shouldReturn(true);
    }

    function it_not_match_when_header_is_missing_in_request()
    {
        $responseConfig = Element::fromArray([
            'request' => [
                'path' => '/foo',
                'headers' => ['Header' => 'Value']
            ],
        ]);
        $request = Request::create('/foo');

        $this->match($request, $responseConfig)->shouldReturn(false);
    }

    function it_not_match_when_header_value_in_request_is_different_than_expected()
    {
        $responseConfig = Element::fromArray([
            'request' => [
                'path' => '/foo',
                'headers' => ['Header' => 'Value']
            ],
        ]);
        $request = Request::create('/foo');
        $request->headers->add(['Header' => 'Different Value']);

        $this->match($request, $responseConfig)->shouldReturn(false);
    }

    function it_match_when_headers_from_config_are_equal_headers_from_request()
    {
        $responseConfig = Element::fromArray([
            'request' => [
                'path' => '/foo',
                'headers' => [
                    'Header' => 'Value',
                    'Header1' => 'Value1'
                ]
            ],
        ]);
        $request = Request::create('/foo');
        $request->headers->add([
            'Header' => 'Value',
            'Header1' => 'Value1'
        ]);

        $this->match($request, $responseConfig)->shouldReturn(true);
    }
}
