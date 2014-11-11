<?php

namespace spec\Coduo\TuTu\Request;

use Coduo\PHPMatcher\Factory\SimpleFactory;
use Coduo\TuTu\Config\Element;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;

class BodyMatchingPolicySpec extends ObjectBehavior
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

    function it_match_when_config_body_is_empty()
    {
        $responseConfig = Element::fromArray([
            'request' => ['path' => '/foo'],
        ]);
        $request = Request::create('/foo');

        $this->match($request, $responseConfig)->shouldReturn(true);
    }

    function it_match_when_request_body_match_config_body()
    {
        $responseConfig = Element::fromArray([
            'request' => [
                'path' => '/foo',
                'headers' => [],
                'body' => 'HELLO WORLD'
            ],
        ]);
        $request = Request::create('/foo', 'GET', [], [], [], [], 'HELLO WORLD');

        $this->match($request, $responseConfig)->shouldReturn(true);
    }

    function it_not_match_when_request_body_not_match_config_body()
    {
        $responseConfig = Element::fromArray([
            'request' => [
                'path' => '/foo',
                'headers' => [],
                'body' => 'HELLO WORLD'
            ],
        ]);
        $request = Request::create('/foo', 'GET', [], [], [], [], 'WORLD HELLO');

        $this->match($request, $responseConfig)->shouldReturn(false);
    }
}
