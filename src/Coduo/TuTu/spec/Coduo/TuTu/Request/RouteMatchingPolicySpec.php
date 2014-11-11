<?php

namespace spec\Coduo\TuTu\Request;

use Coduo\TuTu\Config\Element;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;

class RouteMatchingPolicySpec extends ObjectBehavior
{
    function it_is_matching_policy()
    {
        $this->shouldBeAnInstanceOf('Coduo\TuTu\Request\MatchingPolicy');
    }

    function it_match_when_request_path_is_equal_to_response_config_path()
    {
        $responseConfig = Element::fromArray(['request' => ['path' => '/foo']]);
        $request        = Request::create('/foo');

        $this->match($request, $responseConfig)->shouldReturn(true);
    }

    function it_match_when_request_path_is_equal_to_response_config_path_but_contain_upercase_chars()
    {
        $responseConfig = Element::fromArray(['request' => ['path' => '/FOo']]);
        $request        = Request::create('/foo');

        $this->match($request, $responseConfig)->shouldReturn(true);
    }

    function it_match_paths_with_placeholders()
    {
        $responseConfig = Element::fromArray(['request' => ['path' => '/foo/{id}/bar/{name}']]);
        $request        = Request::create('/foo/1/bar/norbert');

        $this->match($request, $responseConfig)->shouldReturn(true);
    }


    function it_does_not_match_when_request_path_is_not_matching_response_config_path()
    {
        $responseConfig = Element::fromArray(['request' => ['path' => '/foo/bar']]);
        $request        = Request::create('/foo/baz');

        $this->match($request, $responseConfig)->shouldReturn(false);
    }
}
