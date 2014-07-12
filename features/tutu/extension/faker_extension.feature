Feature: Create fake data in responses
  As a api client developer
  In order to simulate simple api behavior
  I need to create response with fake data

  Scenario: Create response from request query parameters
    Given there is a routing file "responses.yml" with following content:
    """
    hello_world:
      path: /hello/world
      content: |
        Hello {{ faker.firstName }}
    """
    And TuTu is running on host "localhost" at port "8000"
    When http client send GET request on "http://localhost:8000/hello/world"
    Then response status code should be 200
    And the response content should match expression:
    """
    Hello ([A-Z]{1}[a-z]+)
    """
