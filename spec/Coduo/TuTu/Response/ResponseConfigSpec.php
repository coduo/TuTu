<?php

namespace spec\Coduo\TuTu\Response;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Routing\Route;

class ResponseConfigSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(new Route('/foo'));
    }

    function it_can_be_created_only_for_specific_methods()
    {
        $this->setAllowedMethods(['POST']);
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
        $this->setAllowedMethods(['post']);
        $this->isMethodAllowed('POST')->shouldReturn(true);
    }

    function it_uppercase_method_during_check()
    {
        $this->setAllowedMethods(['POST']);
        $this->isMethodAllowed('post')->shouldReturn(true);
    }
}
