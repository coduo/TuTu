Feature: Create response from routing.yml file
  As a api client developer
  In order to simulate simple api behavior
  I need to create routing.yml file with responses for specific requests

  Scenario: Include config file in responses.yml file
    Given there is a responses config file "responses.yml" with following content:
    """
    includes:
      - api.yml
    """
    Given there is a responses config file "api.yml" with following content:
    """
    hello_world:
      path: /hello/world
      content: "Hello world"
    """
    And TuTu is running on host "localhost" at port "8000"
    When http client send GET request on "http://localhost:8000/hello/world"
    Then response status code should be 200
    And the response content should be equal to:
    """
    Hello world
    """

  Scenario: Include responses file that include another file
    Given there is a responses config file "responses.yml" with following content:
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
      path: /hello/world
      content: "Hello world"
    """
    And TuTu is running on host "localhost" at port "8000"
    When http client send GET request on "http://localhost:8000/hello/world"
    Then response status code should be 200
    And the response content should be equal to:
    """
    Hello world
    """
