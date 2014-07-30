Feature: Create response from routing.yml file
  As a api client developer
  In order to simulate simple api behavior
  I need to create routing.yml file with responses for specific requests

  Background:
    Given TuTu is running on host "localhost" at port "8000"

  Scenario: Create response with default response code and static body
    When there is a responses config file "responses.yml" with following content:
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
    And http client sends GET request on "http://localhost:8000/hello/world"
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
    When there is a responses config file "responses.yml" with following content:
    """
    empty:
      request:
        path: /empty
      response:
        status: 204
    """
    And http client sends GET request on "http://localhost:8000/empty?wqeqwqw=qweq"
    Then response status code should be 204
    And the response content should be empty

  Scenario: Create response with custom headers
    When there is a responses config file "responses.yml" with following content:
    """
    json:
      request:
        path: /api/json
      response:
        headers:
          "Content-Type": "application/json"
    """
    And http client sends GET request on "http://localhost:8000/api/json"
    Then response status code should be 200
    And the response content should be empty
    And response should have following hedaers:
      | Name         | Value            |
      | Content-Type | application/json |
