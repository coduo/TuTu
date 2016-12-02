Feature: Create response for specific request parameters
  As a api client developer
  In order to simulate simple api behavior
  I need to create be able to create different responses based on request parameters

  Background:
    Given TuTu is running on host "localhost" at port "8000"

  Scenario: Create response when request query parameters match
    When there is a responses config file "responses.yml" with following content:
    """
    hello_world_with_param:
      request:
        path: /request
        query:
          hello: world
      response:
        content: "Hello Foo"

    request_with_param:
      request:
        path: /request
        query:
          param: foo
      response:
        content: "Hello Foo"
    """
    And http client sends GET request on "http://localhost:8000/request?param=foo"
    Then response status code should be 200
    And the response content should be equal to:
    """
    Hello Foo
    """

  Scenario: Create response when request query parameters match pattern
    When there is a responses config file "responses.yml" with following content:
    """
    hello_world_with_param:
      request:
        path: /request
        query:
          param: "@string@"
      response:
        content: "Hello Foo"

    request_with_param:
      request:
        path: /request
      response:
        content: "Hello World"
    """
    And http client sends GET request on "http://localhost:8000/request?param=foo"
    Then response status code should be 200
    And the response content should be equal to:
    """
    Hello Foo
    """

  Scenario: Create response when request body parameters match
    When there is a responses config file "responses.yml" with following content:
    """
    request_with_query_param:
      request:
        path: /request
        query:
          param: foo
      response:
        content: "Hello Foo"

    request_with_body_param:
      request:
        path: /request
        request:
          param: foo
      response:
        content: "Hello Foo"
    """
    And http client sends POST request on "http://localhost:8000/request" with following parameters:
      | Parameter | Value   |
      | param     | foo     |
    Then response status code should be 200
    And the response content should be equal to:
    """
    Hello Foo
    """

  Scenario: Create response when request body parameters match pattern
    When there is a responses config file "responses.yml" with following content:
    """
    request_with_query_param:
      request:
        path: /request
        request:
          param: "@string@"
      response:
        content: "Hello Foo"

    request_with_body_param:
      request:
        path: /request
      response:
        content: "Hello World"
    """
    And http client sends POST request on "http://localhost:8000/request" with following parameters:
      | Parameter | Value   |
      | param     | foo     |
    Then response status code should be 200
    And the response content should be equal to:
    """
    Hello Foo
    """

  Scenario: Create request when body and query parameters does not match
    When there is a responses config file "responses.yml" with following content:
    """
    request_with_query_param:
      request:
        path: /request
        query:
          param: foo
      response:
        content: "Hello Foo"

    request_with_body_param:
      request:
        path: /request
        request:
          param: foo
      response:
        content: "Hello Foo"

    request_without_params:
      request:
        path: /request
      response:
        content: "Hello world"
    """
    And http client sends GET request on "http://localhost:8000/request"
    Then response status code should be 200
    And the response content should be equal to:
    """
    Hello world
    """
