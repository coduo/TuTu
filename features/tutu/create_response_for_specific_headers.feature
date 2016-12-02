Feature: Create response for specific headers
  As a api client developer
  In order to simulate simple api behavior
  I need to create be able to create different responses based on request headers

  Background:
    Given TuTu is running on host "localhost" at port "8000"

  Scenario: Create response when request headers
    When there is a responses config file "responses.yml" with following content:
    """
    hello_world_with_param:
      request:
        path: /request
        headers:
          Hello: Foo
      response:
        content: "Hello Foo"

    request_with_param:
      request:
        path: /request
        headers:
          Hello: World
      response:
        content: "Hello World"
    """
    And http client sends GET request on "http://localhost:8000/request" with following headers
      | Header | Value |
      | Hello  | World |
    Then response status code should be 200
    And the response content should be equal to:
    """
    Hello World
    """

  Scenario: Create response when request headers match pattern
    When there is a responses config file "responses.yml" with following content:
    """
    hello_world_with_param:
      request:
        path: /request
        headers:
          Hello: "@string@"
      response:
        content: "Hello Foo"

    request_with_param:
      request:
        path: /request
        headers:
          Hello: World
      response:
        content: "Hello World"
    """
    And http client sends GET request on "http://localhost:8000/request" with following headers
      | Header | Value |
      | Hello  | World |
    Then response status code should be 200
    And the response content should be equal to:
    """
    Hello Foo
    """
