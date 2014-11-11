<?php

namespace spec\Coduo\TuTu\Config\Element;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ResponseSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Coduo\TuTu\Config\Element\Response');
    }
}
