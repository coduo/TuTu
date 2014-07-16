<?php

namespace spec\Coduo\TuTu\Config\Element;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RequestSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('/foo');
    }

    function it_throw_exception_when_created_with_not_string_route()
    {
        $this->shouldThrow(new \InvalidArgumentException('Path must be a valid string.'))->during('__construct', [new \DateTime()]);
    }

    function it_throw_exception_when_created_with_empty_route()
    {
        $this->shouldThrow(new \InvalidArgumentException('Path can\'t be empty.'))->during('__construct', ['']);
    }

    function it_always_uppercase_allowed_methods()
    {
        $this->setAllowedMethods(['post', 'GeT']);
        $this->getAllowedMethods()->shouldReturn(['POST', 'GET']);
    }

    function it_always_return_path_with_slash_at_the_beginning()
    {
        $this->beConstructedWith('foo');
        $this->getPath()->shouldReturn('/foo');
    }

    function it_always_return_path_with_single_slash_at_the_beginning()
    {
        $this->beConstructedWith('//foo');
        $this->getPath()->shouldReturn('/foo');
    }
}
