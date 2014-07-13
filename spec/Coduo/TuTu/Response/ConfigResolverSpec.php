<?php

namespace spec\Coduo\TuTu\Response;

use Coduo\TuTu\Response\Config\Loader;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

class ConfigResolverSpec extends ObjectBehavior
{
    function let(Loader $loader)
    {
        $loader->getResponsesArray()->willReturn([]);
        $this->beConstructedWith($loader);
        $r = new RouteCollection();
        $loader->getRouteCollection()->willReturn($r);
    }

    function it_resolve_config_when_method_and_request_uri_fits_configuration(Loader $loader, UrlMatcher $matcher)
    {
        $loader->getResponsesArray()->willReturn([
            [
                'path' => '/foo/index',
                'methods' => ['POST']
            ]
        ]);

        $routeCollection = new RouteCollection();
        $routeCollection->add('foo_index', new Route('/foo/index'));
        $loader->getRouteCollection()->willReturn($routeCollection);

        $request = Request::create('/foo/index', 'POST');

        $this->resolveResponseConfig($request)->shouldReturnAnInstanceOf('Coduo\TuTu\Response\ResponseConfig');
    }

    function it_return_null_when_cant_resolve_response_config()
    {
        $request = Request::create('/foo/index', 'POST');

        $this->resolveResponseConfig($request)->shouldReturn(null);
    }
}
