Feature: Create echo response
  As a api client developer
  In order to simulate simple api behavior
  I need to create response configuration that returns data from request

  Scenario: Create response from request parameters
    Given there is a "custom_config/responses.yml" file with following content
    """
    hello_world:
      request:
        path: /hello/world
      response:
        content: "Hello world!"
    """
    And there is a config file "config.yml" with following content:
    """
    parameters:
      responses_file_path: "%workDir%/custom_config/responses.yml"
    """

    And TuTu is running on host "localhost" at port "8000"
    When http client sends GET request on "http://localhost:8000/hello/world"
    Then response status code should be 200
    And the response content should be equal to:
    """
    Hello world!
    """

  Scenario: Create response from resource file in custom resources path
    Given there is a "custom_resources/hello_world.html.twig" file with following content
    """
    Hello world!
    """
    And there is a "custom_config/responses.yml" file with following content
    """
    hello_world:
      request:
        path: /hello/world
      response:
        content: "@resources/hello_world.html.twig"
    """
    And there is a config file "config.yml" with following content:
    """
    parameters:
      resources_path: "%workDir%/custom_resources"
      responses_file_path: "%workDir%/custom_config/responses.yml"

    extensions:
      Coduo\TuTu\Extension\Faker: ~
    """

    And TuTu is running on host "localhost" at port "8000"
    When http client sends GET request on "http://localhost:8000/hello/world"
    Then response status code should be 200
    And the response content should be equal to:
    """
    Hello world!
    """
