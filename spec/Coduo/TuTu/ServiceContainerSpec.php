<?php

namespace spec\Coduo\TuTu;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ServiceContainerSpec extends ObjectBehavior
{
    function it_can_store_parameters()
    {
        $this->hasParameter('key')->shouldreturn(false);
        $this->setParameter('key', 'value');
        $this->hasParameter('key')->shouldReturn(true);
        $this->getParameter('key')->shouldReturn('value');
    }

    function it_throws_exception_when_parameter_does_not_exist()
    {
        $this->shouldThrow(new \RuntimeException("Service container does not have parameter with id \"key\""))
            ->during('getParameter', ['key']);
    }

    function it_create_service_from_definition()
    {
        $this->hasService('service')->shouldReturn(false);
        $this->setDefinition('service', function($container) {
            return new \stdClass();
        });
        $this->hasService('service')->shouldReturn(true);
        $this->getService('service')->shouldReturnAnInstanceOf('stdClass');
    }

    function it_throws_exception_when_service_does_not_exist()
    {
        $this->shouldThrow(new \RuntimeException("Service container does not have service with id \"key\""))
            ->during('getService', ['key']);
    }

    function it_create_new_service_each_time()
    {
        $this->setDefinition('service', function($container) {
            return new \stdClass();
        });

        $service1 = $this->getService('service');
        $service2 = $this->getService('service');

        $service1->shouldNotBe($service2);
    }

    function it_can_return_same_object_each_time_when_service_definition_is_static()
    {
        $this->setStaticDefinition('service', function($container) {
            return new \stdClass();
        });

        $service1 = $this->getService('service');
        $service2 = $this->getService('service');

        $service1->shouldBe($service2);
    }

    function it_can_return_services_by_tags()
    {
        $this->setDefinition(
            'service_0',
            function($container) {
                return new \stdClass();
            },
            ['foo']
        );
        $this->setDefinition(
            'service_1',
            function($container) {
                return new \DateTime();
            },
            ['foo']
        );

        $services = $this->getServicesByTag('foo');
        $services[0]->shouldReturnAnInstanceOf('stdClass');
        $services[1]->shouldReturnAnInstanceOf('DateTime');
    }

    function it_return_empty_array_when_cant_find_services_by_tag()
    {
        $this->getServicesByTag('foo')->shouldHaveCount(0);
    }

    function it_can_remove_service()
    {
        $this->setDefinition(
            'service',
            function($container) {
                return new \stdClass();
            },
            ['foo']
        );
        $this->removeService('service');
        $this->hasService('service')->shouldReturn(false);
    }
}
