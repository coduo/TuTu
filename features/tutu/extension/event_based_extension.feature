Feature: Simple extension that handle TuTu events
  As a api client developer
  In order modify TuTu behavior
  I need to be able to handle TuTu events

  Background:
    Given there is a responses config file "responses.yml" with following content:
    """
    hello_world:
      request:
        path: /hello/world
      response:
        content: "Hello world!"
    """
    And there is a config file "config.yml" with following content:
    """
    autoload:
      "TuTu": "%workDir%/src/"

    extensions:
      TuTu\Extension: ~
    """

  Scenario: Change response after successful request matching
    Given there is a "src/TuTu/Extension.php" file with following content
    """
    <?php

    namespace TuTu;

    use Coduo\TuTu\Extension as TuTuExtension;
    use Coduo\TuTu\ServiceContainer;

    class Extension implements TuTuExtension
    {
        public function load(ServiceContainer $container)
        {
            $container->setDefinition('response_subscriber', function($c) {
                $c->getService('class_loader')->loadClass('TuTu\ResponseEventSubscriber');
                return new ResponseEventSubscriber();
            }, ['event_dispatcher.subscriber']);
        }
    }
    """
    And there is a "src/TuTu/ResponseEventSubscriber.php" file with following content
    """
    <?php

    namespace TuTu;

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Coduo\TuTu\Config\Element\Response;
    use Coduo\TuTu\Event\RequestMatch;
    use Coduo\TuTu\Events;

    class ResponseEventSubscriber implements EventSubscriberInterface
    {
        public static function getSubscribedEvents()
        {
            return [
                Events::REQUEST_MATCH => 'onRequestMatch'
            ];
        }

        public function onRequestMatch(RequestMatch $event)
        {
            $event->getConfigElement()->changeResponse(
                Response::fromArray([
                    'content' => 'Yo!'
                ])
            );
        }
    }
    """
    And TuTu is running on host "localhost" at port "8000"
    When http client sends GET request on "http://localhost:8000/hello/world"
    Then response status code should be 200
    And the response content should be equal to:
    """
    Yo!
    """

  Scenario: Change request before config element is resolved
    Given there is a "src/TuTu/Extension.php" file with following content
    """
    <?php

    namespace TuTu;

    use Coduo\TuTu\Extension as TuTuExtension;
    use Coduo\TuTu\ServiceContainer;

    class Extension implements TuTuExtension
    {
        public function load(ServiceContainer $container)
        {
            $container->setDefinition('response_subscriber', function($c) {
                $c->getService('class_loader')->loadClass('TuTu\RequestEventSubscriber');
                return new RequestEventSubscriber();
            }, ['event_dispatcher.subscriber']);
        }
    }
    """
    And there is a "src/TuTu/RequestEventSubscriber.php" file with following content
    """
    <?php

    namespace TuTu;

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Coduo\TuTu\Config\Element\Response;
    use Symfony\Component\HttpFoundation\Request;
    use Coduo\TuTu\Event\PreConfigResolve;
    use Coduo\TuTu\Events;

    class RequestEventSubscriber implements EventSubscriberInterface
    {
        public static function getSubscribedEvents()
        {
            return [
                Events::PRE_CONFIG_RESOLVE => 'onPreConfigResolve'
            ];
        }

        public function onPreConfigResolve(PreConfigResolve $event)
        {
            $event->changeRequest(Request::create('/hello/world', 'GET'));
        }
    }
    """
    And TuTu is running on host "localhost" at port "8000"
    When http client sends POST request on "http://localhost:8000/invalid/path"
    Then response status code should be 200
    And the response content should be equal to:
    """
    Hello world!
    """
