Feature: Create response for specific body
  As a api client developer
  In order to simulate simple api behavior
  I need to create be able to create different responses based on request body

  Background:
    Given TuTu is running on host "localhost" at port "8000"

  Scenario: Create response when request body match
    When there is a responses config file "responses.yml" with following content:
    """
    hello_world_with_param:
      request:
        path: /register
        body: |
          {
            "email": "norbert@coduo.pl",
            "plainPassword": "password123",
            "firstName": "Norbert",
            "lastName": "Orzechowicz"
          }
      response:
        content: "Hello Norbert"

    request_with_param:
      request:
        path: /register
      response:
        content: "Hello World"
    """
    And http client sends POST request on "http://localhost:8000/register" with body
    """
    {
      "email": "norbert@coduo.pl",
      "plainPassword": "password123",
      "firstName": "Norbert",
      "lastName": "Orzechowicz"
    }
    """
    Then response status code should be 200
    And the response content should be equal to:
    """
    Hello Norbert
    """

  Scenario: Create response when request body match pattern
    When there is a responses config file "responses.yml" with following content:
    """
    hello_world_with_param:
      request:
        path: /register
        body: |
          {
            "id": @integer@,
            "email": "@string@",
            "plainPassword": "@string@",
            "firstName": "@string@",
            "lastName": "@string@"
          }
      response:
        content: "Hello Norbert"

    request_with_param:
      request:
        path: /register
      response:
        content: "Hello World"
    """
    And http client sends POST request on "http://localhost:8000/register" with body
    """
    {
      "id" : 1,
      "email": "norbert@coduo.pl",
      "plainPassword": "password123",
      "firstName": "Norbert",
      "lastName": "Orzechowicz"
    }
    """
    Then response status code should be 200
    And the response content should be equal to:
    """
    Hello Norbert
    """
