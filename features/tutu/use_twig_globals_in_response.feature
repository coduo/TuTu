Feature: Create response with twig global
  As a api client developer
  In order to use defined global variables in twig
  I need to configure those variables in twig configuration

  Scenario: Create response with twig global
    Given there is a config file "config.yml" with following content:
    """
    parameters:
      twig:
        globals:
          domain: "http://domain.com"
    """
    And TuTu is running on host "localhost" at port "8000"
    And there is a responses config file "responses.yml" with following content:
    """
    hello_world:
      request:
        path: /hello/world
      response:
        content: |
          Welcome on {{ domain }}!
    """
    When http client sends POST request on "http://localhost:8000/hello/world" with following parameters:
      | Parameter | Value   |
      | name      | Norbert |
    Then response status code should be 200
    And the response content should be equal to:
    """
    Welcome on http://domain.com!
    """

  Scenario: Create response with twig global that value is a valid parameter
    Given there is a config file "config.yml" with following content:
    """
    parameters:
      application.domain: "http://domain.com"
      twig:
        globals:
          domain: %application.domain%
    """
    And TuTu is running on host "localhost" at port "8000"
    And there is a responses config file "responses.yml" with following content:
    """
    hello_world:
      request:
        path: /hello/world
      response:
        content: |
          Welcome on {{ domain }}!
    """
    When http client sends POST request on "http://localhost:8000/hello/world" with following parameters:
      | Parameter | Value   |
      | name      | Norbert |
    Then response status code should be 200
    And the response content should be equal to:
    """
    Welcome on http://domain.com!
    """
