<?php

namespace spec\Coduo\TuTu\Config\Loader;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class YamlLoaderSpec extends ObjectBehavior
{
    private $responsesYaml;

    function let()
    {
        $this->responsesYaml = __DIR__ . '/../../../../config/responses.yml.dist';
        $this->beConstructedWith($this->responsesYaml);
    }

    function it_is_loader()
    {
        $this->shouldBeAnInstanceOf('Coduo\TuTu\Config\Loader\Loader');
    }

    function it_throws_exception_when_invalid_responses_yaml_path()
    {
        $notExistingFilePath = dirname($this->responsesYaml . '/this_file_does_not_exists');
        $expectedMessage = sprintf("File \"%s\" does not exist.", $notExistingFilePath);
        $this->shouldThrow(new \InvalidArgumentException($expectedMessage))
            ->during('__construct', [$notExistingFilePath]);
    }
}
