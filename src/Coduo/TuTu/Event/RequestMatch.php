<?php

namespace Coduo\TuTu\Event;

use Coduo\TuTu\Config\Element;
use Symfony\Component\EventDispatcher\Event;

class RequestMatch extends Event
{
    /**
     * @var \Coduo\TuTu\Config\Element
     */
    private $configElement;

    public function __construct(Element $configElement)
    {
        $this->configElement = $configElement;
    }

    /**
     * @return \Coduo\TuTu\Config\Element
     */
    public function getConfigElement()
    {
        return $this->configElement;
    }
}
