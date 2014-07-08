<?php

namespace spec\Coduo\TuTu\Response;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ResponseConfigSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('/foo');
    }

    function it_throw_exception_when_created_with_not_string_route()
    {
        $this->shouldThrow(new \InvalidArgumentException('Route must be a valid string.'))->during('__construct', [new \DateTime()]);
    }

    function it_throw_exception_when_created_with_empty_route()
    {
        $this->shouldThrow(new \InvalidArgumentException('Route can\'t be empty.'))->during('__construct', ['']);
    }

    function it_match_routes_with_trimmed_slashes()
    {
        $this->beConstructedWith('foo');
        $this->routeMatch('/foo/')->shouldReturn(true);
        $this->routeMatch('/foo')->shouldReturn(true);
        $this->routeMatch('foo')->shouldReturn(true);
    }

    function it_match_routes_with_named_placeholders()
    {
        $this->beConstructedWith('/foo/info/{id}');
        $this->routeMatch('/foo/info/10')->shouldReturn(true);
        $this->routeMatch('/foo/info/ASC')->shouldReturn(true);
        $this->routeMatch('/foo/info/a-qwe')->shouldReturn(true);
        $this->routeMatch('/foo/info/1/test')->shouldReturn(false);
    }

    function it_can_be_created_only_for_specific_methods()
    {
        $this->beConstructedWith('/foo', ['POST']);
        $this->isMethodAllowed('POST')->shouldReturn(true);
        $this->isMethodAllowed('GET')->shouldReturn(false);
    }

    function it_allows_all_methods_by_default()
    {
        $this->isMethodAllowed('DELETE')->shouldReturn(true);
        $this->isMethodAllowed('POST')->shouldReturn(true);
        $this->isMethodAllowed('GET')->shouldReturn(true);
    }

    function it_always_uppercase_allowed_methods()
    {
        $this->beConstructedWith('/foo', ['post']);
        $this->isMethodAllowed('POST')->shouldReturn(true);
    }

    function it_uppercase_method_during_check()
    {
        $this->beConstructedWith('/foo', ['POST']);
        $this->isMethodAllowed('post')->shouldReturn(true);
    }
}
