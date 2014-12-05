Feature: Create response from external file
  As a api client developer
  In order to keep TuTu configuration as small as possible
  I need to load response content from external file using {% include %}

  Background:
    Given there is a config file "config.yml" with following content:
    """
    parameters:
      twig:
        globals:
          domain: "http://domain.com"
    """
    And TuTu is running on host "localhost" at port "8000"

  Scenario: Create response from resource file
    Given there is a responses config file "responses.yml" with following content:
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
