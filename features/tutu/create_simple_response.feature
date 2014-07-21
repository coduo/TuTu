Feature: Create response from routing.yml file
  As a api client developer
  In order to simulate simple api behavior
  I need to create routing.yml file with responses for specific requests

  Scenario: Create response with default response code and static body
    Given there is a responses config file "responses.yml" with following content:
    """
    hello_world:
      request:
        path: /hello/world
      response:
        content: |
          <!DOCTYPE html>
          <html>
          <head>
            <title>Title of the document</title>
          </head>
          <body>
            <h1>Hello World!</h1>
          </body>
          </html>
    """
    And TuTu is running on host "localhost" at port "8000"
    When http client send GET request on "http://localhost:8000/hello/world"
    Then response status code should be 200
    And the response content should be equal to:
    """
    <!DOCTYPE html>
    <html>
    <head>
      <title>Title of the document</title>
    </head>
    <body>
      <h1>Hello World!</h1>
    </body>
    </html>
    """

  Scenario: Create response with custom status code
    Given there is a responses config file "responses.yml" with following content:
    """
    empty:
      request:
        path: /empty
      response:
        status: 204
    """
    And TuTu is running on host "localhost" at port "8000"
    When http client send GET request on "http://localhost:8000/empty?wqeqwqw=qweq"
    Then response status code should be 204
    And the response content should be empty

  Scenario: Create response with custom headers
    Given there is a responses config file "responses.yml" with following content:
    """
    json:
      request:
        path: /api/json
      response:
        headers:
          "Content-Type": "application/json"
    """
    And TuTu is running on host "localhost" at port "8000"
    When http client send GET request on "http://localhost:8000/api/json"
    Then response status code should be 200
    And the response content should be empty
    And response should have following hedaers:
      | Name         | Value            |
      | Content-Type | application/json |

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
