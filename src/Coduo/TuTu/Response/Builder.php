<?php

namespace Coduo\TuTu\Response;

use Coduo\TuTu\Config\Element;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Builder
{
    /**
     * @var \Twig_Environment
     */
    private $twig;

    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param Element $config
     * @param Request $request
     * @return Response
     */
    public function build(Element $config, Request $request)
    {
        $content = $this->twig->render($config->getResponse()->getContent(), [
            'request' => $request
        ]);

        return new Response($content, $config->getResponse()->getStatus(), $config->getResponse()->getHeaders());
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function buildForMismatch(Request $request)
    {
        $content = sprintf('TuTu don\'t know how to response for "%s" "%s" request :(', $request->getMethod(), $request->getRequestUri());
        return new Response(
            $content,
            404,
            ['TuTu-Error' => 'Request mismatch']
        );
    }

    /**
     * @param \Exception $exception
     * @return Response
     */
    public function buildForException(\Exception $exception)
    {
        $content = sprintf('There was a internal server error with message: %s', $exception->getMessage());
        return new Response(
            $content,
            500,
            ['TuTu-Error' => 'Internal']
        );
    }
}
