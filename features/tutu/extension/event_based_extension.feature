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
        content: "Hello world"
    """
    And there is a config file "config.yml" with following content:
    """
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
                return new EventSubscriber();
            }, ['event_dispatcher.subscriber']);
        }
    }
    """
    And there is a "src/TuTu/EventSubscriber.php" file with following content
    """
    <?php

    namespace TuTu;

    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Coduo\TuTu\Config\Element\Response;

    class EventSubscriber implements EventSubscriberInterface
    {
        public static function getSubscribedEvents()
        {
            return [
                'tutu.request.match' => 'onRequestMatch'
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
    When http client send GET request on "http://localhost:8000/hello/world"
    Then response status code should be 200
    And the response content should be equal to:
    """
    Yo!
    """
