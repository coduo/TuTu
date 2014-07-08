<?php

namespace spec\Coduo\TuTu\Response;

use Coduo\TuTu\Response\ResponseConfig;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;

class ConfigResolverSpec extends ObjectBehavior
{
    function it_resolve_config_when_method_and_request_uri_fits_configuration(Request $request)
    {
        $request->getMethod()->willReturn('POST');
        $request->getPathInfo()->willReturn('/foo/index');
        $config = new ResponseConfig('/foo/index', ['POST']);
        $this->addResponseConfig($config);

        $this->resolveResponse($request)->shouldReturn($config);
    }

    function it_return_null_when_cant_resolve_response_config(Request $request)
    {
        $request->getMethod()->willReturn('POST');
        $request->getPathInfo()->willReturn('/foo/index');

        $this->resolveResponse($request)->shouldReturn(null);
    }
}
