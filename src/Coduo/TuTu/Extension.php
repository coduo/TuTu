<?php

namespace Coduo\TuTu;

use Pimple\Container;

interface Extension
{
    /**
     * @param Container $container
     */
    public function load(Container $container);
}
