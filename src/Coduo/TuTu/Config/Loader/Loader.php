<?php

namespace Coduo\TuTu\Config\Loader;

interface Loader
{
    /**
     * @return []
     */
    public function getResponsesArray();
}
