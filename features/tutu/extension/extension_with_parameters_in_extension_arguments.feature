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

    parameters:
      extension.response: "Extension Response!"

    extensions:
      TuTu\Extension:
        responseContent: "%extension.response%"
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
        private $responseContent;

        public function __construct($responseContent)
        {
            $this->responseContent = $responseContent;
        }

        public function load(ServiceContainer $container)
        {
            $container->setDefinition('response_subscriber', function($c) {
                $c->getService('class_loader')->loadClass('TuTu\ResponseEventSubscriber');
                return new ResponseEventSubscriber($this->responseContent);
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
        private $responseContent;

        public function __construct($responseContent)
        {
            $this->responseContent = $responseContent;
        }

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
                    'content' => $this->responseContent
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
    Extension Response!
    """
