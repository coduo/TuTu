<?php

namespace spec\Coduo\TuTu\Request\Path;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;

class ParserSpec extends ObjectBehavior
{
    function it_return_empty_array_when_there_are_no_placeholders_in_url()
    {
        $request = Request::create('/foo');
        $this->extractPlaceholders($request, '/foo')->shouldReturn(array());
    }

    function it_return_values_when_there_are_placeholders_in_url()
    {
        $request = Request::create('/users/email@example.com/tasks/1');
        $this->extractPlaceholders($request, '/users/{email}/tasks/{taskId}')->shouldReturn(array(
            'email' => 'email@example.com',
            'taskId' => '1'
        ));
    }

    function it_return_empty_array_when_request_path_does_not_match_url_pattern()
    {
        $request = Request::create('/users/email@example.com');
        $this->extractPlaceholders($request, '/users/{email}/tasks/{taskId}')->shouldReturn(array());
    }
}

