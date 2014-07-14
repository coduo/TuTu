<?php

namespace Coduo\TuTu;

interface Extension
{
    /**
     * @param ServiceContainer $container
     */
    public function load(ServiceContainer $container);
}
