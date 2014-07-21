Feature: Create response for specific request parameters
  As a api client developer
  In order to simulate simple api behavior
  I need to create be able to create different responses based on request parameters

  Scenario: Create response when request query parameters match
    Given there is a responses config file "responses.yml" with following content:
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
    And TuTu is running on host "localhost" at port "8000"
    When http client send GET request on "http://localhost:8000/request?param=foo"
    Then response status code should be 200
    And the response content should be equal to:
    """
    Hello Foo
    """

  Scenario: Create response when request body parameters match
    Given there is a responses config file "responses.yml" with following content:
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
    And TuTu is running on host "localhost" at port "8000"
    When http client send POST request on "http://localhost:8000/request" with following parameters:
      | Parameter | Value   |
      | param     | foo     |
    Then response status code should be 200
    And the response content should be equal to:
    """
    Hello Foo
    """

  Scenario: Create request when body and query parameters does not match
    Given there is a responses config file "responses.yml" with following content:
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
    And TuTu is running on host "localhost" at port "8000"
    When http client send GET request on "http://localhost:8000/request"
    Then response status code should be 200
    And the response content should be equal to:
    """
    Hello world
    """
