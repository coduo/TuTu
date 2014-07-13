<?php

namespace Coduo\TuTu\Response\Config;

interface Loader
{
    /**
     * @return []
     */
    public function getResponsesArray();
}
