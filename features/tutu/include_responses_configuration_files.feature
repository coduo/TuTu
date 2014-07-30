Feature: Create response from routing.yml file
  As a api client developer
  In order to simulate simple api behavior
  I need to create routing.yml file with responses for specific requests

  Background:
    Given TuTu is running on host "localhost" at port "8000"

  Scenario: Include config file in responses.yml file
    When there is a responses config file "responses.yml" with following content:
    """
    includes:
      - api.yml
    """
    And there is a responses config file "api.yml" with following content:
    """
    hello_world:
      request:
        path: /hello/world
      response:
        content: "Hello world"
    """
    When http client sends GET request on "http://localhost:8000/hello/world"
    Then response status code should be 200
    And the response content should be equal to:
    """
    Hello world
    """

  Scenario: Include responses file that include another file
    When there is a responses config file "responses.yml" with following content:
    """
    includes:
      - api/api.yml
    """
    And there is a responses config file "api/api.yml" with following content:
    """
    includes:
      - user.yml
    """
    And there is a responses config file "api/user.yml" with following content:
    """
    hello_world:
      request:
        path: /hello/world
      response:
        content: "Hello world"
    """
    When http client sends GET request on "http://localhost:8000/hello/world"
    Then response status code should be 200
    And the response content should be equal to:
    """
    Hello world
    """
