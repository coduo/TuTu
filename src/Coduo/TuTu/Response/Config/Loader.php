<?php

namespace Coduo\TuTu\Response\Config;

interface Loader
{
    /**
     * @return []
     */
    public function getResponsesArray();

    /**
     * @return \Symfony\Component\Routing\RouteCollection
     */
    public function getRouteCollection();
}
