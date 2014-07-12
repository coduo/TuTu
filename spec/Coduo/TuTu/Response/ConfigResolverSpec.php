<?php

namespace spec\Coduo\TuTu\Response;

use Coduo\TuTu\Response\Config\Loader;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;

class ConfigResolverSpec extends ObjectBehavior
{
    function let(Loader $loader)
    {
        $loader->getResponsesArray()->willReturn([]);
        $this->beConstructedWith($loader);
    }

    function it_resolve_config_when_method_and_request_uri_fits_configuration(Request $request, Loader $loader)
    {
        $loader->getResponsesArray()->willReturn([
            [
                'path' => '/foo/index',
                'methods' => ['POST']
            ]
        ]);

        $request->getMethod()->willReturn('POST');
        $request->getPathInfo()->willReturn('/foo/index');

        $this->resolveResponseConfig($request)->shouldReturnAnInstanceOf('Coduo\TuTu\Response\ResponseConfig');
    }

    function it_return_null_when_cant_resolve_response_config(Request $request)
    {
        $request->getMethod()->willReturn('POST');
        $request->getPathInfo()->willReturn('/foo/index');

        $this->resolveResponseConfig($request)->shouldReturn(null);
    }
}
