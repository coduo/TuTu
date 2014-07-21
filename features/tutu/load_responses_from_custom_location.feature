Feature: Create echo response
  As a api client developer
  In order to simulate simple api behavior
  I need to create response configuration that returns data from request

  Scenario: Create response from request parameters
    Given there is a "config/responses.yml" file with following content
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
      responses_file_path: "%workDir%/config/responses.yml"
    """

    And TuTu is running on host "localhost" at port "8000"
    When http client send GET request on "http://localhost:8000/hello/world"
    Then response status code should be 200
    And the response content should be equal to:
    """
    Hello world!
    """
